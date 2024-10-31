<?php

namespace App\Console\Commands;

use App\Common\Models\{
    Message,
    MessageReceiver,
    Project\Project,
    Project\ProjectMember,
    Project\ProjectSupplier,
    Project\ProjectSupplierStatistic
};
use App\Jobs\SendMailJob;
use App\Modules\Admin\Repository\{
    Project\ProjectDecisionEntryRepo,
    Project\ProjectDecisionRepo,
    Project\ProjectDecisionSupplierRepo,
    Project\ProjectOpenRepo,
    Project\ProjectPublishRepo,
    Project\ProjectSupplierRepo
};
use Illuminate\Console\Command;
use Illuminate\Contracts\Bus\Dispatcher;

// 后台用户数据导入
class ProjectCommand extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'project:operate {action}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'project operate';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $action = $this->argument('action');
        switch ($action) {
            case 'publish':
                $this->publish();
                break;
            case 'quote':
                $this->quote();
                break;
            case 'open':
                $this->open();
                break;
            case 'statistic':
                $this->statistic();
                break;
            case 'invitation':
                $this->invitation();
                break;
            case 'expired':
                try {
                    $this->expired();
                } catch (Exception $ex) {
                    Illuminate\Support\Facades\Log::channel('command')->info(__CLASS__ . '   ' . __FUNCTION__ . '    ' . $ex->getMessage());
                }
                break;
        }
    }

    public function evaluation() {
        $nextTime = date('Y-m-d', strtotime('+1 days'));
        $time = date('Y-m-d H:i:s');
        $bidBillObj = Project::selectRaw('bid_publish,id')
                ->whereRaw('bill_status=\'C\'')
                ->whereRaw('current_step=\'I\'')
                ->whereRaw('bid_evaluation=\'0\'')
                ->whereRaw('bid_evaluation_date<\'' . $nextTime . '\'')
                ->get();
        if (empty($bidBillObj)) {
            return;
        }
        $decisionRepo = new ProjectDecisionRepo();
        $projectList = $bidBillObj->toArray();
        foreach ($projectList as $project) {
            $project['bid_evaluation'] = 1;
            $project['current_step'] = 'K';
            $project['updated_at'] = $time;
            Project::where('id', $project['id'])->update($project);
            $decisionRepo->Init($project['id']);
            (new ProjectDecisionEntryRepo)->init($project['id']);
            (new ProjectDecisionSupplierRepo)->init($project['id']);
        }
    }

    public function publish() {
        $time = date('Y-m-d H:i:s');
        try {
            $query = Project::selectRaw('bid_publish,id')
                    ->whereRaw('bill_status=\'C\'')
                    ->whereRaw('bid_publish=\'1\'')
                    ->whereRaw('current_step=\'F\'')
                    ->whereRaw('bid_open_deadline<\'' . $time . '\'');
            $bidBillObj = $query->get();
        } catch (Exception $ex) {
            Illuminate\Support\Facades\Log::channel('command')->info(__CLASS__ . '   ' . __FUNCTION__ . '    ' . $ex->getMessage());
        }
        if (empty($bidBillObj)) {
            return;
        }
        $openRepo = new ProjectOpenRepo();
        $projectList = $bidBillObj->toArray();
        foreach ($projectList as $project) {
            $project['current_step'] = 'H';
            $project['updated_at'] = $time;
            Project::where('id', $project['id'])->update($project);
            $openRepo->Init($project['id']);
        }
    }

    public function quote() {
        try {
            $projectObj = Project::selectRaw('bid_publish,id,bid_open_deadline,name,email,org_id,contact_id')
                    ->whereRaw('bill_status=\'C\'')
                    ->whereRaw('current_step IN (\'F\',\'K\')')
                    ->whereRaw('bid_publish=\'1\'')
                    ->whereRaw('bid_open_deadline >=\'' . date('Y-m-d H:i:s', strtotime(' -1 minutes')) . '\' AND bid_open_deadline <=\'' . date('Y-m-d H:i:s') . '\'')
                    ->get();


            if (empty($projectObj)) {
                return;
            }
            $projectList = $projectObj->toArray();
            foreach ($projectList as $project) {
                $supplierObj = ProjectSupplier::where('project_id', $project['id'])
                        ->selectRaw('supplier_id,supplier_name')
                        ->whereRaw('is_tender=\'2\'')
                        ->whereRaw('shortlist_flag=\'Y\'')
                        ->get();
                $supplierList = $supplierObj->toArray();
                if (!empty($supplierList)) {
                    foreach ($supplierList as $supplier) {
                        $this->sendUnQuoteMessage($project, $supplier);
                        $this->sendUnQuoteMail($project, $supplier);
                    }
                }
            }
        } catch (Exception $ex) {
            Illuminate\Support\Facades\Log::channel('command')->info(__CLASS__ . '   ' . __FUNCTION__ . '    ' . $ex->getMessage());
        }
    }

    public function sendUnQuoteMessage($projectData, $supplier) {
        $bossUrl = env('BOSS_URL');
        $messageId = Message::insertGetId([
                    'receiver_type' => 'PURCHASER',
                    'content_url' => $bossUrl . '/front/#/inviteTenders/ProjectApprovalDetails?id=' . $projectData['id'],
                    'sender_id' => $projectData['org_id'],
                    'message_type' => 'SYSTEM',
                    'message_title' => '【' . env('APP_NAME') . '】【' . $projectData['name'] . '】【' . $supplier['supplier_name'] . '】未投标',
                    'message' => '【' . $projectData['name'] . '】的招标项目，【' . $supplier['supplier_name'] . '】未投标，请确认未投标原因，请尽快登录系统查看信息。',
                    'created_at' => date('Y-m-d H:i:s'),
        ]);
        $data = [
            'message_id' => $messageId,
            'receiver_id' => $projectData['contact_id'],
            'supplier_id' => $supplier['supplier_id'],
            'org_id' => $projectData['org_id'],
            'read_flag' => 'N',
            'created_at' => date('Y-m-d H:i:s')
        ];
        MessageReceiver::insert($data);
    }

    public function sendUnQuoteMail($projectData, $supplier) {
        if (!empty($projectData['email'])) {
            app(Dispatcher::class)->dispatch
                    (new SendMailJob([
                'projectData' => $projectData,
                'email' => $projectData['email'],
                'supplierName' => $supplier['supplier_name'],
                    ], 'PROJECT_UNQUOTE'));
        }
    }

    public function open() {
        try {
            $nextTime = date('Y-m-d', strtotime('-20 minutes'));
            $time = date('Y-m-d');
            $projectObj = Project::selectRaw('bid_publish,id,bid_open_deadline,name,org_id')
                    ->whereRaw('bill_status=\'C\'')
                    ->whereRaw('current_step=\'F\'')
                    ->whereRaw('bid_publish=\'0\'')
                    ->whereRaw('bid_open_deadline between \'' . $nextTime . '\' AND \'' . $time . '\'')
                    ->get();
            if (empty($projectObj)) {
                return;
            }
            $projectList = $projectObj->toArray();
            $projectIds = array_column($projectList, 'id');
            $userIds = ProjectMember::whereIn('project_id', $projectIds)
                    ->whereRaw('find_in_set(\'H\',resp_business)')
                    ->pluck('user_id');
            foreach ($projectList as $project) {

                $this->sendOpenMessage($project, $userIds);
                $this->sendOpenMail($project, $userIds);
            }
        } catch (Exception $ex) {
            Illuminate\Support\Facades\Log::channel('command')->info(__CLASS__ . '   ' . __FUNCTION__ . '    ' . $ex->getMessage());
        }
    }

    public function sendOpenMessage($project, $userIds) {
        $bossUrl = env('BOSS_URL');
        $messageId = Message::insertGetId([
                    'receiver_type' => 'PURCHASER',
                    'content_url' => $bossUrl . '/front/#/inviteTenders/BidOpening?id=' . $project['id'],
                    'sender_id' => 1,
                    'message_type' => 'SYSTEM',
                    'message_title' => '招标项目【' . $project['name'] . '】将在20分钟后开标。',
                    'message' => '您好，招标项目【' . $project['name'] . '】将在20分钟后开标。请尽快登录系统完成开标。',
                    'created_at' => date('Y-m-d H:i:s'),
        ]);

        if (empty($userIds)) {
            return;
        }
        $dataList = [];
        foreach ($userIds as $userId) {
            $dataList[] = [
                'message_id' => $messageId,
                'receiver_id' => $userId,
                'org_id' => $project['org_id'],
                'read_flag' => 'N',
                'created_at' => date('Y-m-d H:i:s')
            ];
        }
        if (!empty($dataList)) {
            MessageReceiver::insert($dataList);
        }
    }

    public function sendOpenMail($project, $userIds) {
        app(Dispatcher::class)->dispatch
                (new SendMailJob([
            'projectData' => $project,
            'userIds' => $userIds,
                ], 'PROJECT_OPEN_DEADLINE'));
    }

    public function statistic() {
        try {
            $time = date('Y-m-d H:i:s');
            $projectTable = (new Project())->getTable();
            $psupplierTable = (new ProjectSupplier())->getTable();
            $bidBillObj = ProjectSupplier::from($psupplierTable . ' AS ps')
                    ->join($projectTable . ' AS p', function($join) {
                        $join->on('p.id', '=', 'ps.project_id');
                    })
                    ->selectRaw('ps.supplier_id,p.org_id,'
                            . 'sum(if(ps.`status`=\'F\',1,0)) AS won_qty,'
                            . 'sum(if(ps.shortlist_flag=\'Y\',1,0)) AS nomination_qty')
                    ->groupBy('ps.supplier_id')
                    ->groupBy('p.org_id')
                    ->get();
            if (empty($bidBillObj)) {
                return;
            }
            $projectList = $bidBillObj->toArray();

            $dataList = [];
            foreach ($projectList as $project) {
                $project['created_at'] = $time;
                $project['updated_at'] = $time;
                $dataList[] = $project;
            }
            ProjectSupplierStatistic::upsert($dataList, ['supplier_id', 'org_id'], ['won_qty', 'nomination_qty', 'updated_at', 'org_id']);
        } catch (Exception $ex) {
            Illuminate\Support\Facades\Log::channel('command')->info(__CLASS__ . '   ' . __FUNCTION__ . '    ' . $ex->getMessage());
        }
    }

    public function invitation() {
        try {
            $time = date('Y-m-d H:i:s');
            $projectObj = Project::selectRaw('bid_document,id')
                    ->where('supplier_invi_end_date', '<', $time)
                    ->where('supplier_invitation', '0')
                    ->where('bill_status', 'C')
                    ->whereIn('current_step', ['B', 'B,C'])
                    ->get();
            if (empty($projectObj)) {
                return;
            }
            $porjectList = $projectObj->toArray();
            $projectIds = array_column($porjectList, 'id');
            Project::whereIn('id', $projectIds)
                    ->update(['shortlist_at' => date('Y-m-d H:i:s')]);
            //未入围
            ProjectSupplier::whereIn('project_id', $projectIds)
                    ->where('enroll_status', 'Y')
                    ->where('shortlist_flag', 'N')
                    ->update(['status' => 'J']);
            $supplierRepo = new ProjectSupplierRepo();
            $publichRepo = new ProjectPublishRepo();
            foreach ($porjectList as $porject) {
                $data = [
                    'supplier_invitation' => '1',
                    'shortlist_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
                $data['current_step'] = $porject['bid_document'] == '1' ? 'F' : 'C';
                Project::where('id', $porject['id'])->update($data);
                $publichRepo->init($porject['id']);
                $supplierRepo->sends($porject['id']);
            }
        } catch (Exception $ex) {
            Illuminate\Support\Facades\Log::channel('command')->info(__CLASS__ . '   ' . __FUNCTION__ . '    ' . $ex->getMessage());
        }
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function expired() {
        $supplierTable = (new ProjectSupplier)->getTable();
        $projectTable = (new Project())->getTable();
        $projectObj = Project::from($projectTable . ' as p')
                ->join($supplierTable . ' as ps', function($join) {
                    $join->on('p.id', '=', 'ps.project_id');
                })
                ->selectRaw('ps.id')
                ->whereRaw('p.enroll_deadline < \'' . date('Y-m-d H:i:s') . '\'')
                ->whereRaw('ps.invitation_status=\'C\'')
                ->whereRaw('(ps.status=\'T\' OR ps.status=\'WCY\'OR ps.status=\'N\') ')
                ->get();
        if (empty($projectObj)) {
            return;
        }
        $projectList = $projectObj->toArray();
        foreach ($projectList as $project) {
            ProjectSupplier::where('id', $project['id'])->update(['invitation_status' => 'N',
                'updated_at' => date('Y-m-d H:i:s')]);
        }
    }

}
