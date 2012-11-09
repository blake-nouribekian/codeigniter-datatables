<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Data extends CI_Controller
{    
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }
    
    public function index()
    {

    }

    public function getTable()
    {
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
         */
        $aColumns = array('id', 'first_name', 'last_name');
        
        // DB table to use
        $sTable = 'data_table';
        
        // Paging
        if(isset($_GET['iDisplayStart']) && $_GET['iDisplayLength'] != '-1')
        {
            $this->db->limit($this->db->escape_str($_GET['iDisplayLength']), $this->db->escape_str($_GET['iDisplayStart']));
        }
        
        // Ordering
        if(isset($_GET['iSortCol_0']))
        {
            for($i=0; $i<intval($_GET['iSortingCols']); $i++)
            {
                if($_GET['bSortable_'.intval($_GET['iSortCol_'.$i])] == 'true')
                {
                    $this->db->order_by($aColumns[intval($this->db->escape_str($_GET['iSortCol_'.$i]))], $this->db->escape_str($_GET['sSortDir_'.$i]));
                }
            }
        }
        
        // Individual column filtering
        if(isset($_GET['sSearch']) && !empty($_GET['sSearch']))
        {
            for($i=0; $i<count($aColumns); $i++)
            {
                if(isset($_GET['bSearchable_'.$i]) && $_GET['bSearchable_'.$i] == 'true')
                {
                    $this->db->or_like($aColumns[$i], $this->db->escape_like_str($_GET['sSearch']));
                }
            }
        }
        
        // Select data
        $this->db->select('SQL_CALC_FOUND_ROWS '.str_replace(' , ', ' ', implode(', ', $aColumns)), false);
        $rResult = $this->db->get($sTable);

        // Data set length after filtering
        $this->db->select('FOUND_ROWS() AS found_rows');
        $iFilteredTotal = $this->db->get()->row()->found_rows;

        // Total data set length
        $iTotal = $this->db->count_all($sTable);

        // Output
        $output = array(
            'sEcho' => intval($_GET['sEcho']),
            'iTotalRecords' => $iTotal,
            'iTotalDisplayRecords' => $iFilteredTotal,
            'aaData' => array()
        );
        
        foreach($rResult->result_array() as $aRow)
        {
            $row = array();
            
            foreach($aColumns as $col)
            {
                $row[] = $aRow[$col];
            }

            $output['aaData'][] = $row;
        }

        echo json_encode($output);
    }
}
?>