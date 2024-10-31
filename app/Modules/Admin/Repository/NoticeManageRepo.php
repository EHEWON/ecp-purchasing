<?php

namespace App\Modules\Admin\Repository;

use App\Common\Contracts\Repository;
use App\Common\Helpers\Excel;
use App\Common\Models\{
    Notice,
    NoticeSub
};
use App\Modules\Admin\Repository\Inquiry\InquiryRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Style\{
    Alignment,
    Border,
    Font
};

class NoticeManageRepo extends Repository {

    protected $model;
    protected $sorts = [
        'bill_no',
        'title',
        'org_id',
        'bill_date',
        'is_top',
        'due_date',
        'src_bill_no',
        'sup_scope',
        'biz_type',
    ];

    public function __construct() {
        $this->model = new Notice();
        parent::__construct($this->model);
    }

    protected function getOrder(&$query, Request $request) {
        /**
         * 排序
         */
        $query->orderBy('n.is_top', 'DESC');
        $sort = !empty($request->sort) && in_array(strtolower(trim($request->sort)), $this->sorts) ? trim($request->sort) : 'bill_date';
        $order = !empty($request->order) && in_array(strtolower(trim($request->order)), ['desc', 'asc']) ? trim($request->order) : 'DESC';
        $query->orderBy($sort, $order);
        if ($sort !== 'bill_date') {
            $query->orderBy('bill_date', 'DESC');
        }
    }

    /**
     * @param Request $request
     * @param string $filed
     * @return array
     */
    public function getList(Request $request) {
        $noticeTable = $this->model->getTable();
        $query = $this->model
                ->selectRaw('n.id,n.bill_no,n.bill_date,n.due_date,n.biz_type,'
                        . 'n.biz_type,n.sup_scope,n.bill_status,n.org_id,n.title,n.is_top')
                ->from($noticeTable . ' as n');
        $this->getWhere($query, $request);
        $clone = $query->clone();
        $total = $clone->count();
        $this->getPage($query, $request);
        $this->getOrder($query, $request);
        $object = $query->get();
        if (empty($object)) {
            return ['data' => [], 'total' => $total];
        }
        $data = $object->toArray();
        $inquiryRepo = new InquiryRepo();
        foreach ($data as &$item) {
            $item['biz_type_name'] = $this->getBizTypeText($item['biz_type']);
            $item['bill_status_name'] = $this->getStatusText($item['bill_status']);
            $item['sup_scope_name'] = $inquiryRepo->getSupScopeText($item['sup_scope']);
        }
        $list = [];
        $list['total'] = $total;
        $list['data'] = $data;
        return $list;
    }

    /**
     * @param Request $request
     * @param string $filed
     * @return array
     */
    public function info($id) {

        $noticeTable = $this->model->getTable();
        $subTable = (new NoticeSub)->getTable();
        $query = $this->model
                ->selectRaw('*')
                ->from($noticeTable . ' as n')
                ->join($subTable . ' as ns', function($join) {
                    $join->on('n.id', '=', 'ns.notice_id');
                })
                ->where('n.id', $id);
        $object = $query->first();
        if (empty($object)) {
            return [];
        }
        $data = $object->toArray();
        $inquiryRepo = new InquiryRepo();
        $data['src_bill_id'] = (string) $data['src_bill_id'];
        $data['notice_id'] = (string) $data['notice_id'];
        $data['modifier_id'] = (string) $data['modifier_id'];
        $data['auditor_id'] = (string) $data['auditor_id'];
        $data['creator_id'] = (string) $data['creator_id'];
        $data['biz_type_name'] = $this->getBizTypeText($data['biz_type']);
        $data['bill_status_name'] = $this->getStatusText($data['bill_status']);
        $data['sup_scope_name'] = (new InquiryRepo)->getSupScopeText($data['src_bill_type']);
        $data['src_bill_type_name'] = $this->getSrcBillTypeText($data['src_bill_type']);
        $data['sup_scope_name'] = $inquiryRepo->getSupScopeText($data['sup_scope']);
        $data['attach'] = (new NoticeAttachRepo)->getList($id);
        return $data;
    }

    /**
     * @param int $noticeId
     * @param Request $request
     * 
     * @return array
     */
    public function edited($noticeId, Request $request) {
        $admin = Auth::guard('admin')->user();
        $flag = Notice::where('id', $noticeId)->update([
            'due_date' => !empty($request->due_date) ? trim($request->due_date) : null,
            'org_id' => !empty($request->org_id) ? trim($request->org_id) : 1,
            'biz_type' => !empty($request->biz_type) ? trim($request->biz_type) : 1,
            'sup_scope' => !empty($request->sup_scope) ? trim($request->sup_scope) : 1,
            'content' => !empty($request->content) ? trim($request->content) : '',
            'bill_status' => !empty($request->bill_status) ? trim($request->bill_status) : 'A',
            'bill_type_id' => !empty($request->bill_type_id) ? intval($request->bill_type_id) : 0,
            'title' => !empty($request->title) ? trim($request->title) : '',
            'remark' => !empty($request->remark) ? trim($request->remark) : '',
            'is_top' => !empty($request->is_top) ? trim($request->is_top) : '0',
            'updated_by' => $admin->user_id,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        NoticeSub::where('notice_id', $noticeId)->update([
            'modifier_id' => $admin->user_id,
            'modify_time' => date('Y-m-d H:i:s'),
            'src_bill_id' => !empty($request->src_bill_id) ? trim($request->src_bill_id) : 0,
            'src_bill_no' => !empty($request->src_bill_no) ? trim($request->src_bill_no) : '',
            'src_bill_type' => !empty($request->src_bill_type) ? trim($request->src_bill_type) : '',
            'title' => !empty($request->title) ? trim($request->title) : '',
            'remark' => !empty($request->remark) ? trim($request->remark) : 1,
            'title' => !empty($request->title) ? trim($request->title) : '',
            'remark' => !empty($request->remark) ? trim($request->remark) : '',
        ]);
        (new NoticeAttachRepo)->updateData($noticeId, $request);
//        (new NoticeSupplierRepo)->updateData($noticeId, $request);
        return $flag;
    }

    /**
     * @param Request $request
     * @param string $filed
     * @return array
     */
    public function addData(Request $request) {
        $admin = Auth::guard('admin')->user();
        $noticeId = Notice::insertGetId([
                    'bill_no' => $this->getNoticeNo(),
                    'bill_date' => date('Y-m-d H:i:s'),
                    'due_date' => !empty($request->due_date) ? trim($request->due_date) : null,
                    'org_id' => !empty($request->org_id) ? trim($request->org_id) : 1,
                    'biz_type' => !empty($request->biz_type) ? trim($request->biz_type) : 1,
                    'sup_scope' => !empty($request->sup_scope) ? trim($request->sup_scope) : 1,
                    'content' => !empty($request->content) ? trim($request->content) : 1,
                    'bill_status' => !empty($request->bill_status) ? trim($request->bill_status) : 'A',
                    'cfm_status' => !empty($request->cfm_status) ? trim($request->cfm_status) : 'A',
                    'cfm_status' => 'A',
                    'bill_type_id' => !empty($request->bill_type_id) ? intval($request->bill_type_id) : 0,
                    'title' => !empty($request->title) ? trim($request->title) : '',
                    'remark' => !empty($request->remark) ? trim($request->remark) : '',
                    'is_top' => !empty($request->is_top) ? trim($request->is_top) : '0',
        ]);
        NoticeSub::where('notice_id', $noticeId)->delete();
        NoticeSub::insert([
            'notice_id' => $noticeId,
            'creator_id' => $admin->user_id,
            'create_time' => date('Y-m-d H:i:s'),
            'src_bill_id' => !empty($request->src_bill_id) ? trim($request->src_bill_id) : 0,
            'src_bill_no' => !empty($request->src_bill_no) ? trim($request->src_bill_no) : '',
            'src_bill_type' => !empty($request->src_bill_type) ? trim($request->src_bill_type) : '',
            'title' => !empty($request->title) ? trim($request->title) : '',
            'remark' => !empty($request->remark) ? trim($request->remark) : 1,
        ]);
        (new NoticeAttachRepo)->updateData($noticeId, $request);
//        (new NoticeSupplierRepo)->updateData($noticeId, $request);
        return $noticeId;
    }

    /**
     * @param Array $ids
     * @param string $enable
     * @return array
     */
    public function topping(Request $request) {
        $admin = Auth::guard('admin')->user();
        $ids = $request->ids;
        return Notice::whereIn('id', $ids)->update([
                    'is_top' => 1,
                    'updated_by' => $admin->user_id,
                    'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * @param Array $ids
     * @param string $enable
     * @return array
     */
    public function cancel(Request $request) {
        $admin = Auth::guard('admin')->user();
        $ids = $request->ids;
        return Notice::whereIn('id', $ids)->update([
                    'is_top' => 0,
                    'updated_by' => $admin->user_id,
                    'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * @param Array $ids
     * @param string $enable
     * @return array
     */
    public function deleteData(Request $request) {
        $ids = $request->ids;
        return Notice::whereIn('id', $ids)->delete();
    }

    /**
     * @param $query
     * @param Request $request
     * @param bool $statusFlag
     */
    protected function getWhere(&$query, Request $request) {
        if (!empty($request->keyword)) {
            $keyword = trim($request->keyword);
            $query->where(function ($q)use($keyword) {
                $q->where('title', 'like', '%' . $keyword . '%')
                        ->orWhere('bill_no', 'like', '%' . $keyword . '%');
            });
        }
        if (!empty($request->bill_status)) {
            $status = $request->bill_status;
            $statusies = is_array($status) ? $status : explode(',', trim($status));
            $query->whereIn('bill_status', $statusies);
        }
        if (!empty($request->biz_type)) {
            $bizType = $request->biz_type;
            $bizTypes = is_array($bizType) ? $bizType : explode(',', trim($bizType));
            $query->whereIn('biz_type', $bizTypes);
        }
        if (!empty($request->org_id)) {
            $orgId = $request->org_id;
            $orgIds = is_array($orgId) ? $orgId : explode(',', trim($orgId));
            $query->whereIn('org_id', $orgIds);
        }

        if (!empty($request->createtype)) {
            $createAts = $this->getTimeByType($request->createtype);
            $createAts[1] = date('Y-m-d 23:59:59');
            $query->whereBetween('bill_date', $createAts);
        } elseif (!empty($request->createtime)) {
            $createtime = $request->createtime;
            $createAts = is_array($createtime) ? $createtime : explode(',', $createtime);
            !empty($createAts[1]) ? $createAts[1] = date('Y-m-d 23:59:59', strtotime($createAts[1])) : $createAts[1] = date('Y-m-d H:i:s');
            $query->whereBetween('bill_date', $createAts);
        }
    }

    /**
     * 获取生成的询价单流水号
     * @author liujf 2017-06-20
     * @return string $inquirySerialNo 询价单流水号
     */
    public function getNoticeNo(&$newNumber = null) {
        $prefix = 'GG';
        $qurey = $this->model->selectRaw('*');
        $number = $newNumber ? $newNumber : $qurey
                        ->where('bill_no', 'like', $prefix . '%')
                        ->orderBy('bill_no', 'DESC')
                        ->value('bill_no');
        if (!empty($number)) {
            $date = substr($number, 2, 8);
            $serialSetp = substr($number, 10, 5);
            $step = intval($serialSetp);
            $step ++;
            return $this->createSerialNo($step, $prefix, $date);
        }
        return $this->createSerialNo(1, $prefix, '');
    }

    /**
     * 生成流水号
     * @param string $step 需要补零的字符
     * @param string $prefix 前缀
     * @author liujf 2019-03-11
     * @return string $code
     */
    private function createSerialNo($step = 1, $prefix = '', $date = '') {
        $time = date('Ymd');
        if (empty($date) || $date < $time) {
            $step = 1;
        }
        $pad = str_pad($step, 5, '0', STR_PAD_LEFT);
        return$prefix . $time . $pad;
    }

    public function getStatusText($status) {
        switch (strtoupper($status)) {
            case 'A':
                return '保存';
            case 'B':
                return '已提交';
            case 'C':
                return '已审核';
        }
    }

    public function getStatusList() {
        return [
            'A' => '保存',
            'B' => '已提交',
            'C' => '已审核',
        ];
    }

    /**
     * 
     * 1询价公告 2招标公告 3 竞价公告 4比价公告 5 中标公告  6招募公告 7 行业动态 8系统公告  A 询价结果公告  B竞价结果公告
     */
    public function getBizTypeText($bizType) {
        switch (strtoupper($bizType)) {
            case '1':
                return '询价公告';
            case '2':
                return '招标公告';
            case '3':
                return '竞价公告';
            case '4':
                return '比价公告';
            case '5':
                return '中标公告';
            case '6':
                return '招募公告';
            case '7':
                return '行业动态';
            case '8':
                return '系统公告';
            case 'A':
                return '询价结果公告';
            case 'B':
                return '竞价结果公告';
        }
    }

    /**
     * 
     * 1询价公告 2招标公告 3 竞价公告 4比价公告 5 中标公告  6招募公告 7 行业动态 8系统公告  A 询价结果公告  B竞价结果公告
     */
    public function getSrcBillTypeText($srcBillType) {
        switch (strtolower($srcBillType)) {
            case 'sou_inquiry':
                return '询价';
            case 'sou_compare':
                return '比价';
        }
    }

    public function getBizTypeList() {
        return ['1' => '询价公告',
            '2' => '招标公告',
            '3' => '竞价公告',
            '4' => '比价公告',
            '5' => '中标公告',
            '6' => '招募公告',
            '7' => '行业动态',
            '8' => '系统公告',
            'A' => '询价结果公告',
            'B' => '竞价结果公告',
        ];
    }

    public function getSrcBillTypeList() {
        return ['sou_inquiry' => '询价',
            'sou_compare' => '比价'
        ];
    }

    /**
     * 导出
     * @param $request
     * @return array
     */
    public function export(Request $request) {
        $noticeTable = $this->model->getTable();
        $subTable = (new NoticeSub)->getTable();
        $query = $this->model
                ->selectRaw('n.id,n.bill_no,n.bill_date,n.due_date,'
                        . 'n.biz_type,n.sup_scope,n.bill_status,n.org_id,n.title,'
                        . 'ns.src_bill_id,ns.src_bill_no,ns.src_bill_type,n.is_top')
                ->from($noticeTable . ' as n')
                ->join($subTable . ' as ns', function($join) {
            $join->on('n.id', '=', 'ns.notice_id');
        });
        if ($request->type === 'ALL') {
            $query->where('deleted_flag', 'N');
        } elseif ($request->ids) {
            $query->where('deleted_flag', 'N')
                    ->whereIn('id', $request->ids);
        } else {
            $this->getWhere($query, $request);
        }
        $this->getOrder($query, $request);
        $object = $query->get();

        if (empty($object)) {
            return [];
        }
        $data = $object->toArray();
        $inquiryRepo = new InquiryRepo();
        foreach ($data as &$item) {
            $item['src_bill_id'] = (string) $item['src_bill_id'];
            $item['biz_type_name'] = $this->getBizTypeText($item['biz_type']);
            $item['bill_status_name'] = $this->getStatusText($item['bill_status']);
            $item['src_bill_type_name'] = $this->getSrcBillTypeText($item['src_bill_type']);
            $item['sup_scope_name'] = $inquiryRepo->getSupScopeText($item['sup_scope']);
        }
        $headName = $this->getHeadName();
        $xlsName = "notice_" . date("YmdHis", time()) . uniqid(); //文件名称
        return $this->downloadExcel($xlsName, $data, $headName);
    }

    private $styleArray = [
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER,
            'wrapText' => true,
        ],
        'font' => [
            'name' => 'Arial',
            'bold' => false,
            'italic' => false,
            'size' => 9,
            'underline' => Font::UNDERLINE_NONE,
            'strikethrough' => false,
            'color' => [
                'rgb' => '000000'
            ]
        ],
        'numberFormat' => ['formatCode' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT],
        'borders' => [
            'outline' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['argb' => '00000000'],
            ],
        ],
    ];

    public function setExcelRow($sheet, $col, $row, $value, $width) {
        $sheet->setCellValue($col . $row, $value);
        $sheet->getStyle($col . $row)->applyFromArray($this->styleArray);
        $sheet->getColumnDimension($col)->setWidth($width);
    }

    /**
     * 导出
     * @param type $request Description
     * @param $name
     * @param array $data
     * @param array $head
     * @return array
     */
    public function downloadExcel($name, $data = [], $head = []) {
        $count = count($head);  //计算表头数量
        $spreadsheet = Excel::newSpreadsheet();
        $styleArray = $this->styleArray;
        $sheet = $spreadsheet->getSpreadsheet()->getActiveSheet();
        $sheet->mergeCells('A1:J1');
        $sheet->getStyle('A1')->applyFromArray($styleArray);
        $sheet->setCellValue('A1', '公告');
        for ($i = 65; $i < $count + 65; $i++) {     //数字转字母从65开始
            $this->setExcelRow($sheet, strtoupper(chr($i)), 2, $head[$i - 65], 20);
        }
        $row = 3;
        foreach ($data as $item) {
            //数字转字母从65开始：
            $this->setExcelRow($sheet, 'B', $row, $item['bill_no'], 24);
            $sheet->getStyle('B' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'C', $row, $item['title'], 24);
            $sheet->getStyle('C' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'D', $row, $item['biz_type_name'], 24);
            $sheet->getStyle('D' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'E', $row, $item['sup_scope_name'], 24);
            $sheet->getStyle('E' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'F', $row, $item['bill_status_name'], 24);
            $sheet->getStyle('F' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'G', $row, $item['bill_date'], 24);
            $sheet->getStyle('G' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'H', $row, $item['due_date'], 24);
            $sheet->getStyle('H' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'I', $row, $item['is_top'] == 1 ? '置顶' : '未置顶', 24);
            $sheet->getStyle('I' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'J', $row, $item['src_bill_no'], 24);
            $sheet->getStyle('J' . $row)->applyFromArray($styleArray);
            $row++;
        }
        $spreadsheet
                ->getSpreadsheet()
                ->getActiveSheet()
                ->getStyle('A1:J2')
                ->applyFromArray($styleArray);
        $realtive = "/download/" . date("Ymd") . '/';
        $filename = $name . '.xlsx';
        $filedir = base_path() . '/public' . $realtive;
        @mkdir($filedir, 0777, true);
        $filepath = $filedir . $filename;
        $spreadsheet->save($filepath);
        $url = env('APP_URL') . $realtive . $filename;
        return ['file_url' => $url, 'attach_name' => $filename];
    }

    /**
     * 获取headName
     * @param $data
     * @return array
     */
    public function getHeadName() {
        return [
            '发布组织',
            '单据编号',
            '主题',
            '公告类型',
            '公告范围',
            '发布状态',
            '发布时间',
            '到期时间',
            '置顶',
            '源单单号',
        ];
    }

    /**
     * 审核
     *
     * @return array
     */
    public function audit(Request $request) {
        $noticeId = $request->post('id');
        $status = $request->post('status');
//        $remark = $request->post('remark');
        $audit = Notice::lockForUpdate()
                ->where('deleted_flag', 'N')
                ->where('id', $noticeId)
                ->where('bill_status', 'B')
                ->first();

        check(!empty($audit), Lang::get('response.no_data'));
        if ($status !== 'C') {
            $flag = Notice::where('deleted_flag', 'N')
                    ->where('id', $noticeId)
                    ->where('bill_status', 'B')
                    ->update(['bill_status' => 'A']);
            Notice::sharedLock();
            return $flag;
        }
        $auditFlag = Notice::where('deleted_flag', 'N')
                ->where('id', $noticeId)
                ->where('bill_status', 'B')
                ->update(['bill_status' => 'C']);
        $admin = Auth::guard('admin')->user();
        NoticeSub::where('notice_id', $noticeId)->update([
            'auditor_id' => $admin->user_id,
            'audit_date' => date('Y-m-d H:i:s')
        ]);
        Notice::sharedLock();
        return $auditFlag;
    }

}
