<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Common\Helpers;

/**
 * Description of Page
 *
 * @author azhong
 */
use Illuminate\Support\Facades\Request;

class Page {

    public $firstRow; // 起始行数
    public $listRows; // 列表每页显示行数
    public $default_pagesize; // 默认每页显示条数
    public $parameter; // 分页跳转时要带的参数
    public $totalRows; // 总行数
    public $totalPages; // 分页总页面数
    public $rollPage = 10; // 分页栏每页显示的页数
    public $lastSuffix = false; // 最后一页是否显示总页数
    private $p = 'p'; //分页参数名
    private $url = ''; //当前链接URL
    private $nowPage = 1;
    private $fun = NULL; //js分页方法
    // 分页显示定制
    private $config = array(
        'header' => '<li class="disabled hwh-page-info"><a>  <em>%NOW_PAGE%</em>/%TOTAL_PAGE%</a></li>',
        'prev' => '<',
        'next' => '>',
        'first' => '<<',
        'last' => '>>',
        'theme' => '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%',
    );

    /**
     * 架构函数
     * @param array $totalRows  总的记录数
     * @param array $listRows  每页显示记录数
     * @param array $parameter  分页跳转的参数
     */
    public function __construct($totalRows, $listRows = 10, $parameter = [], $default_pagesize = 10, $rollPage = 10) {
        /* 基础设置 */
        $this->totalRows = $totalRows; //设置总记录数
        $this->listRows = $listRows;  //设置每页显示行数
        $this->parameter = $parameter;
        $this->default_pagesize = $default_pagesize;
        $this->rollPage = $rollPage;
        $this->nowPage = empty(Request::get('page')) ? 1 : intval(Request::get('page'));
        $this->nowPage = $this->nowPage > 0 ? $this->nowPage : 1;
        $this->firstRow = $this->listRows * ($this->nowPage - 1);
    }

    /**
     * 定制分页链接设置
     * @param string $name  设置名称
     * @param string $value 设置值
     */
    public function setConfig($name, $value) {
        if (isset($this->config[$name])) {
            $this->config[$name] = $value;
        }
    }

    /**
     * 生成链接URL
     * @param  integer $page 页码
     * @return string
     */
    private function url($page, $pageSize = false, $url_type = 1) {
        $pagesize = $pageSize ?: $this->listRows;
        if ($this->fun) {
            return 'javascript:' . $this->fun . '(' . $page . ',' . $pagesize . ')';
        }
        $url = $this->url;
        if ($url_type === 2 && $pageSize != $this->listRows) {
            $url = $this->url2;
        }
        $pageStr = str_replace(urlencode('[PAGE]'), $page, str_replace(urlencode('[PAGESIZE]'), $pagesize, $url));
        if (false !== strpos($pageStr, 'page=1&')) {
            $pageStr = str_replace('page=1&', '', $pageStr);
        }

        if (false !== strpos($pageStr, '&pagesize=' . $this->default_pagesize)) {
            // 直接作为路由地址解析
            $pageStr = str_replace('&pagesize=' . $this->default_pagesize, '', $pageStr);
        } elseif (false !== strpos($pageStr, '?pagesize=' . $this->default_pagesize) && strlen($pageStr) - 10 - strlen($this->default_pagesize) !== strpos($pageStr, '?page=1')) {
            $pageStr = str_replace('?pagesize=' . $this->default_pagesize, '?', $pageStr);
        } elseif (false !== strpos($pageStr, '?pagesize=' . $this->default_pagesize)) {
            $pageStr = str_replace('?pagesize=' . $this->default_pagesize, '', $pageStr);
        }
        $pageStr = rtrim($pageStr, '?page=1');
        $pageStr = rtrim($pageStr, '&page=1');
        $pageStr = rtrim($pageStr, '&');
        return rtrim($pageStr, '?');
    }

    /**
     * 生成普通页码按钮
     *
     * @param  string $url
     * @param  int    $page
     * @return string
     */
    protected function getPageLinkWrapper($url, $page) {
        if ($page == $this->currentPage()) {
            return $this->getActivePageWrapper($page);
        }

        return $this->getAvailablePageWrapper($url, $page);
    }

    /**
     * 组装分页链接
     * @return string
     */
    public function show($domain = false, $param = [], $fun = NULL, $moduleFlag = true, $pageSizes = [15, 30, 45, 60]) {
        if (0 == $this->totalRows) {
            return '';
        }
        $this->fun = $fun;
        if (empty($fun)) {

            $baseurl = '/frontend/list?';
            $this->parameter = Request::all();
            $this->parameter = array_merge($this->parameter, $param);
            if (isset($this->parameter['page'])) {
                unset($this->parameter['page']);
            }
            if (isset($this->parameter['rows'])) {
                unset($this->parameter['rows']);
            }
            if (isset($this->parameter['__hash__'])) {
                unset($this->parameter['__hash__']);
            }
            foreach ($this->parameter as $key => $item) {
                $this->parameter[urlencode(urldecode($key))] = is_string($item) ? urlencode(urldecode($item)) : $item;
            }
            $this->parameter['page'] = '[PAGE]';
            if ($this->default_pagesize != $this->listRows) {
                $this->parameter['pagesize'] = '[PAGESIZE]';
            } elseif ($this->default_pagesize > 0) {
                unset($this->parameter['pagesize']);
            }
            $url = uri($baseurl, $this->parameter);
            $parameter = $this->parameter;
            $parameter['pagesize'] = '[PAGESIZE]';
            $url2 = uri($baseurl, $parameter);
            $this->url = $url;
            $this->url2 = $url2;
        }
        /* 计算分页信息 */
        $this->totalPages = ceil($this->totalRows / $this->listRows); //总页数
        if (!empty($this->totalPages) && $this->nowPage > $this->totalPages) {
            $this->nowPage = $this->totalPages;
        }

        /* 计算分页临时变量 */
        $now_cool_page = $this->rollPage / 2;
        $now_cool_page_ceil = ceil($now_cool_page);
        $this->lastSuffix && $this->config['last'] == $this->totalPages;

        //上一页
        $up_row = $this->nowPage - 1;
        $up_page = $up_row > 0 ? '<a class="prev" href="' . $this->url($up_row) . '">' . $this->config['prev'] . '</a>' : '';

        //下一页
        $down_row = $this->nowPage + 1;
        $down_page = ($down_row <= $this->totalPages) ? '<a class="next" href="' . $this->url($down_row) . '">' . $this->config['next'] . '</a>' : '';

        //第一页
        $the_first = '';
        if ($this->totalPages > $this->rollPage && ($this->nowPage - $now_cool_page) >= 1) {
            $the_first = '<a class="first" href="' . $this->url(1) . '">' . $this->config['first'] . '</a>';
        }

        //最后一页
        $the_end = '';
        if ($this->totalPages > $this->rollPage && ($this->nowPage + $now_cool_page) < $this->totalPages) {
            $the_end = '<a class="end" href="' . $this->url($this->totalPages) . '">' . $this->config['last'] . '</a>';
        }

        //数字连接
        $link_page = "";
        for ($i = 1; $i <= $this->rollPage; $i++) {
            if (($this->nowPage - $now_cool_page) <= 0) {
                $page = $i;
            } elseif (($this->nowPage + $now_cool_page - 1) >= $this->totalPages) {
                $page = $this->totalPages - $this->rollPage + $i;
            } else {
                $page = $this->nowPage - $now_cool_page_ceil + $i;
            }
            if ($page > 0 && $page != $this->nowPage) {

                if ($page <= $this->totalPages) {
                    $link_page .= '<a class="num" href="' . $this->url($page) . '">' . $page . '</a>';
                } else {
                    break;
                }
            } else {
                if ($page > 0 && $this->totalPages != 1) {
                    $link_page .= '<span class="current">' . $page . '</span>';
                }
            }
        }

        //替换分页内容
        $page_str = str_replace(
                array('%HEADER%', '%NOW_PAGE%', '%UP_PAGE%', '%DOWN_PAGE%', '%FIRST%', '%LINK_PAGE%', '%END%', '%TOTAL_ROW%', '%TOTAL_PAGE%'), array($this->config['header'], $this->nowPage, $up_page, $down_page, $the_first, $link_page, $the_end, $this->totalRows, $this->totalPages), $this->config['theme']);
        $page_str = $this->bootstrap_page_style("<div>{$page_str}</div>");
        #listRows
        $inputChange = empty($fun) ? 'location.href=\'' . $this->url('[target]') . '\'.replace(\'[target]\',value)' :
                ($fun . '(value,' . $this->listRows . ')');
        $page_left = '<div class="page_box pb_6">';
        $page_left .= '<div class="lint_num_box">';
        $page_left .= '<span class="plr_10 in_block">每页显示:</span>';
        $page_left .= '<span class="plr_10 in_block border_right '
                . ($this->listRows == $pageSizes[0] ? 'active' : '')
                . '"><a href="' . $this->url(1, $pageSizes[0], 2) . '">' . $pageSizes[0] . '</a></span>';
        $page_left .= '<span class="plr_10 in_block border_right '
                . ($this->listRows == $pageSizes[1] ? 'active' : '') . '">'
                . '<a href="' . $this->url(1, $pageSizes[1], 2) . '">' . $pageSizes[1] . '</a></span>';
        $page_left .= '<span class="plr_10 in_block border_right '
                . ($this->listRows == $pageSizes[2] ? 'active' : '') . '">'
                . '<a href="' . $this->url(1, $pageSizes[2], 2) . '">' . $pageSizes[2] . '</a></span>';
        $page_left .= '<span class="plr_10 in_block ' . ($this->listRows == $pageSizes[3] ? 'active' : '') . '">'
                . '<a href="' . $this->url(1, $pageSizes[3], 2) . '">' . $pageSizes[3] . '</a></span>';
        $page_left .= '</div>';
        $page_left .= '<div class="page_box_page">';
        $page_left .= '<div class="pagination">';

        $page_right = '<div class="page_right">' .
                '<span>第</span>' .
                '<input type="text" value="' . $this->nowPage . '" onkeyup="value = value>' . $this->totalPages . '?' . $this->totalPages . ':value.replace(/[^\d\.]/g,\'\')"  onfocus="this.select()" onchange="Number(value)&&(' . $inputChange . ')">页
        </div>';
        $page_str = $page_left . $page_str . '</div>' . '</div>' . $page_right . '</div>';

        return $page_str;
    }

    /**
     * Thinkphp默认分页样式转Bootstrap分页样式
     * @author H.W.H
     * @param string $page_html tp默认输出的分页html代码
     * @return string 新的分页html代码
     */
    function bootstrap_page_style($page_html) {
        if ($page_html) {
            $page_show = str_replace('<div>', '<nav><ul class="pagination">', $page_html);
            $page_show = str_replace('</div>', '</ul></nav>', $page_show);

            $page_show = str_replace('<span class="current">', '<li class="active"><a>', $page_show);
            $page_show = str_replace('</span>', '</a></li>', $page_show);

            $page_show = str_replace(array('<a class="num"', '<a class="prev"', '<a class="next"', '<a class="end"', '<a class="first"'), '<li><a', $page_show);
            $page_show = str_replace('</a>', '</a></li>', $page_show);
            $page_show = str_replace('</li></li>', '</li>', $page_show);
        }
        return $page_show;
    }

}
