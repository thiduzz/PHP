<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ProductsController
 *
 * @author User
 */
require './ConnectionAdapter.php';

class ProductController {

    public $fields = "";
    public $conditions = "";
    public $group = "";
    public $tablename = "";
    public $query = "";

    public function order_d($param) {
        $con = $this->connect();
        $result = mysqli_query($con, "SELECT * FROM thiago.wa_eshop_vyrobky order by " . $param . " DESC");
        while ($row = mysqli_fetch_array($result)) {
            echo $row['nazev'] . " " . $row['id'];
            echo "<br>";
        }
    }

    public function order_c($param) {
        $con = $this->connect();
        $result = mysqli_query($con, "SELECT * FROM thiago.wa_eshop_vyrobky order by " . $param . " ASC");
        while ($row = mysqli_fetch_array($result)) {
            echo $row['nazev'] . " " . $row['id'];
            echo "<br>";
        }
    }

    public function desc_table($param) {
        $con = $this->connect();
        $fields = array();
        $result = mysqli_query($con, "DESCRIBE thiago.wa_eshop_platba");
        while ($row = mysqli_fetch_array($result)) {
            array_push($fields, $row['Field']);
        }
        return $fields;
    }

    public function update($param) {
        $this->connectDibi();
        if (isset($param['condition_var']) && isset($param['condition_val'])) {
            dibi::update($param['tablename'], $param['values'])
                    ->where($param['condition_var'], $param['condition_val'])
                    ->execute();
        } else {
            dibi::update($param['tablename'], $param['values'])
                    ->execute();
        }
    }

    public function filter($param) {
        $con = $this->connect();
        $ordering = "";
        $result = array();
        $condition = "";
        if ($param['val_order'] != "rows") {
            $ordering = "ORDER BY " . $param['val_order'] . " " . $param['order'];
        }
        $keys = array_keys((array) $param['conditions']);
        foreach ((array) $param['conditions'] as $key => $value) {
            if ($condition == "") {
                $condition = $key . " like '%" . $value . "%'";
            } else {
                $condition = $condition . " AND " . $key . " like '%" . $value . "%'";
            }
        }
        $rs = mysqli_query($con, "SELECT * FROM thiago.wa_eshop_platba WHERE " . $condition . " " . $ordering);
        $items = array();
        while ($row = mysqli_fetch_object($rs)) {
            array_push($items, $row);
        }
        $total = 0;
        if ((count($items) / 20) < 1) {
            $total = 1;
        } else {
            $total = ceil(count($items) / 20);
        }

        $result["page"] = $param['page'];
        $result["total"] = (string) $total;
        $result["records"] = (string) count($items);
        if($param['page'] == 1)
        {
        $result["rows"] = array_slice($items, 0, $param['row']);
        }else{
        $result["rows"] = array_slice($items, ($param['page']-1)*$param['row'], $param['page']*$param['row']-1);
        }
        return json_encode($result);
    }

    public function select($param) {
        $con = $this->connect();
        $ordering = "";
        if ($param['val_order'] != "rows") {
            $ordering = "ORDER BY " . $param['val_order'] . " " . $param['order'];
        }
        $rs = mysqli_query($con, "SELECT * FROM thiago.wa_eshop_platba " . $ordering);
        $items = array();

        while ($row = mysqli_fetch_object($rs)) {
            array_push($items, $row);
        }
        $total = 0;
        if ((count($items) / 20) < 1) {
            $total = 1;
        } else {
            $total = ceil(count($items) / 20);
        }

        $result["page"] = $param['page'];
        $result["total"] = (string) $total;
        $result["records"] = (string) count($items);
        if($param['page'] == 1)
        {
        $result["rows"] = array_slice($items, 0, $param['row']);
        }else{
        $result["rows"] = array_slice($items, ($param['page']-1)*$param['row'], $param['page']*$param['row']-1);
        }
        return json_encode($result);
    }

    public function select_specific($param) {
        $onlyallfields = false;
        $plusallfields = false;
        $starcount = 0;
        $header = array();
        $content = array();
//SET QUERY
        if (!isset($param['query'])) {
//INICIO FIELDS
            if (!empty($param['fields'])) {
                if (count($param['fields']) == 1 && strpos($param['fields'][0], '*') !== false) {
                    $onlyallfields = true;
                }
                foreach ($param['fields'] as $key => $value) {
                    if (count($param['fields']) > 1 && strpos($value, '*') !== false && count($param['tablename']) > 1) {
                        $starcount++;
                        $plusallfields = true;
                    }
                    if ($key == 0) {
                        $this->fields = $value;
                    } else {
                        $this->fields = $this->fields . ", " . $value;
                    }
                }
            } else {
                $this->fields = "*";
                $onlyallfields = true;
            }
//FIM FIELDS
//INICIO CONDICOES
            if (!empty($param['conditions']) && $param['conditions'] != null) {
                $this->conditions = "WHERE " . $param['conditions'];
            } else {
                $this->conditions = "";
            }
//FIM CONDICOES
//INICIO GROUP BY
            if (!empty($param['groupby']) && $param['groupby'] != null) {
                foreach ($param['groupby'] as $key => $value) {
                    if ($key == 0) {
                        $this->group = $value;
                    } else {
                        $this->group = $this->group . ", " . $value;
                    }
                }
                $this->group = "GROUP BY " . $this->group;
            } else {
                $this->group = "";
            }
//FIM GROUP BY
//INICIO TABLENAME
            if (!empty($param['tablename']) && $param['tablename'] != null) {

                foreach ($param['tablename'] as $key => $value) {
                    if ($key == 0) {
                        $this->tablename = $value;
                    } else {
                        if (!isset($param['join_type']) && !isset($param['join_condition'])) {
                            die("error! More than one table was defined, but not the relation, please verify the join_type/join_conditions options!");
                        } else {
                            $this->tablename = $this->tablename . " AS " . $param['tablename_alias'][$key - 1] . " " . $param['join_type'] . " JOIN " . $value . " AS " . $param['tablename_alias'][$key] . " ON " . $param['join_condition'];
                        }
                    }
                }
            } else {
                $this->tablename = "thiago.wa_eshop_vyrobky";
            }
//FIM TABLENAME
            $this->query = "SELECT " . $this->fields . " FROM " . $this->tablename . " " . $this->conditions . " " . $this->group;
        } else {
            $this->query = $param['query'];
        }


//SET HEADER
        if (!isset($param['headers'])) {
            if ($onlyallfields == false && $plusallfields == false) {
                foreach ($param['fields'] as $key => $value) {
                    if (isset($param['show_alias_header'])) {
                        if ($param['show_alias_header'] == false) {
                            $pos = strpos($value, ".");
                            if ($pos == 0 || $pos == null) {
                                array_push($header, $value);
                            } else {
                                array_push($header, substr($value, $pos + 1));
                            }
                        } else {
                            array_push($header, $value);
                        }
                    } else {
                        array_push($header, $value);
                    }
                }
            } else {
                if ($onlyallfields == true) {

                    $con = $this->connect();
                    $result = mysqli_query($con, "DESCRIBE " . $this->tablename);
                    while ($row = mysqli_fetch_array($result)) {
                        array_push($header, $row['Field']);
                    }
                }
                if ($plusallfields == true) {
                    foreach ($param['fields'] as $key => $value) {
                        array_push($header, $value);
                    }
                    foreach ($param['fields'] as $key => $value) {
                        $tabela = "";
                        $alias = "";
                        if (strpos($value, '*') !== false) {
                            $alias = substr($value, 0, strpos($value, '.'));
                            if (!isset($param['tablename_alias'])) {
                                die("You should define the table alias!");
                            } else {
                                foreach ($param['tablename_alias'] as $key2 => $value2) {
                                    if ($value2 == $alias) {
                                        $tabela = $param['tablename'][$key2];
                                    }
                                }

                                $index_array = 0;
                                foreach ($header as $key => $value) {
                                    if (strpos($value, ($alias . "*"))) {
                                        $index_array = $key;
                                        break;
                                    }
                                }

                                if (count($header) == $index_array) {
                                    $con = $this->connect();
                                    $result = mysqli_query($con, "DESCRIBE " . $tabela);
                                    $star_array = array();
                                    while ($row = mysqli_fetch_array($result)) {
                                        array_push($star_array, $row['Field']);
                                    }
                                    array_pop($header);
                                    $header = array_merge($star_array, $header);
                                } else {
                                    $firsthalf = array_slice($header, 0, $index_array + 1);
                                    $secondhalf = array_slice($header, $index_array + 1);
                                    array_pop($firsthalf);


                                    $con = $this->connect();
                                    $result = mysqli_query($con, "DESCRIBE " . $tabela);
                                    $star_array = array();
                                    while ($row = mysqli_fetch_array($result)) {
                                        array_push($star_array, $row['Field']);
                                    }
                                    $header = array_merge($firsthalf, $star_array);
                                    $header = array_merge($header, $secondhalf);
                                }
                                foreach ($header as $key2 => $value2) {
                                    if (isset($param['show_alias_header'])) {
                                        if ($param['show_alias_header'] == false) {
                                            $pos = strpos($value2, ".");
                                            if ($pos > 0 || $pos != null) {
                                                $header[$key2] = substr($value2, $pos + 1);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } else {
            if (count($param['headers']) > count($param['fields'])) {
                die("error! More headers than fields!");
            } else {
                $cont = 0;
                foreach ($param['fields'] as $key => $value) {
                    if (isset($param['headers'][$key])) {
                        array_push($header, $param['headers'][$key]);
                    } else {
                        array_push($header, "Filler" . $cont);
                        $cont++;
                    }
                }
            }
        }

//SET CONTENT
        $grid = array();
        $grid->header = $header;
        /**
          echo '<table><tr>';
          foreach ($header as $key => $value) {
          echo '<th>' . $value . '</th>';
          }
          echo '</tr>';
         * 
         */
        $con = $this->connect();
        $result2 = mysqli_query($con, $this->query);
        $content = array();
        while ($row3 = mysqli_fetch_assoc($result2)) {
            array_push($content, $row3);
            /**
              echo '<tr>';
              foreach ($row3 as $key3 => $value3) {
              echo '<td>' . $value3 . '</td>';
              }
              echo "</tr>";
             * 
             */
        }
        $grid->content = $content;
        //echo '</table>';
    }

    public function insert($param) {
        $this->connectDibi();
        foreach ($param['values'] as $key => $value) {
            $table = $param['tablename'][$value['tablequery']];
            unset($value['tablequery']);
            dibi::query('INSERT INTO ' . $table, $value);
            $id = dibi::getInsertId();
            //\Nette\Diagnostics\Debugger::barDump($value, "title2");
            //\Nette\Diagnostics\Debugger::barDump($id, "title2");
        }
    }

    public function delete($param) {
        $this->connectDibi();
        if (isset($param['condition_var']) && isset($param['condition_val'])) {
            dibi::delete($param['tablename'])
                    ->where($param['condition_var'], $param['condition_val'])
                    ->execute();
        } else {
            dibi::delete($param['tablename'])
                    ->execute();
        }
    }

    public function connect() {
        $obj = new ConnectionAdapter();
        $con = $obj->connect();
        if ($con != null) {
            return $con;
        } else {
            die("Error");
        }
    }

    public function connectDibi() {
        $obj = new ConnectionAdapter();
        $obj->connect_dibi();
    }

}

//-------------DELETE--------------------------------------------
/*
 * $frontController = new DataFrontController(array(
  "controller" => "product",
  "action" => "delete",
  "params" => array(array(
  'tablename' => 'wa_eshop_ucto_zbozi',
  'condition_var' => 'id_nove = %i',
  'condition_val' => 59251
  ))));
  $frontController->run();

  $frontController = new DataFrontController(array(
  "controller" => "product",
  "action" => "delete",
  "params" => array(array(
  'tablename' => 'wa_eshop_ucto_zbozi'
  ))));
  $frontController->run();
 */
//-------------UPDATE--------------------------------------------
/**
 * 
 * Support only one where clause 
 * 
 * $frontController = new DataFrontController(array(
  "controller" => "product",
  "action" => "update",
  "params" => array(array(
  'tablename' => 'wa_eshop_ucto_zbozi',
  'values' => array('id_zakaznik' => 1, 'id_zbozi' => 1, 'ucto_kod_skladu' => 'ATUALIZOU'),
  'condition_var' => 'id_nove = %i',
  'condition_val' => 1
  ))));
  $frontController->run();

  /**Without where will update all the table rows

  $frontController = new DataFrontController(array(
  "controller" => "product",
  "action" => "update",
  "params" => array(array(
  'tablename' => 'wa_eshop_ucto_zbozi',
  'values' => array('id_zakaznik' => 1, 'id_zbozi' => 1, 'ucto_kod_skladu' => 'ATUALIZOU')
  ))));
  $frontController->run();
 */
//-------------INSERT--------------------------------------------
/**
 *
 * Table 1 = tablequery => 0 = wa_eshop_ucto_zbozi
 * Table 2 = tablequery => 1 = wa_eshop_registrace_priznak
 * Allow to insert multiple values in multiple tables in a single call

 * $frontController = new DataFrontController(array(
  "controller" => "product",
  "action"     => "insert",
  "params"     => array(array(
  'tablename'=> array('wa_eshop_ucto_zbozi',
  'wa_eshop_registrace_priznak'),
  'values'=> array(array('tablequery'=>0,  'id_zakaznik' =>1, 'id_zbozi' =>1, 'ucto_kod_skladu' =>'Yooomama'),
  array('tablequery'=>1, 'id' =>1, 'id_zakaznik' =>1,  'priznak' =>'noooomama')))
  )));
 */
//-------------ORDER_C, ORDER_D, DESCRIBE------------------------

/* $frontController = new DataFrontController(array(
  "controller" => "product",
  "action"     => "order_c",
  "params"     => array("id_nove")
  ));
  $frontController->run();
 *  
 *
  // ------------SPECIFIC SELECT ---------------------------------
 * 
  //DO NOT use "*" to select all fields
  //Maximum of 1 join
  //If specified alias, all fields should have the alias
  // optional: conditions, groupby, headers, fields (if not joining)
  // required: if specified 2 tables, MUST HAVE tablename_alias, join_condition, join_type
  //
  // 'fields' => array('A.*'),
  // 'conditions' => null,
  // 'groupby'=>array('A.id_zakaznik', 'A.nazev'),
  // 'tablename'=>array('thiago.wa_eshop_vyrobky','second_table')
  // 'tablename_alias'=>array('A', 'B');
  // 'headers' => array('coluna1', 'coluna2'),
  // 'join_condition' => 'A.id_zakaznik = B.id_zakaznik' / null
  // 'join_type' => 'left'/'right'
  // 'show_alias_header' => true
 * 
 * CALL NORMAIS
  $frontController = new DataFrontController(array(
  "controller" => "product",
  "action"     => "select_specific",
  "params"     => array(array(
  'fields' => array('A.id_nove', 'A.nazev','A.bezna_cena','A.nase_cena','A.bazar','A.dph', 'B.nazev_platba', 'B.platba' ),
  'conditions' => null,
  'tablename'=> array('thiago.wa_eshop_vyrobky', 'thiago.wa_eshop_platba'),
  'tablename_alias'=> array('A', 'B'),
  'join_type' => 'left',
  'join_condition' => 'A.id_zakaznik = B.id_zakaznik',
  'show_alias_header' => false)
  )));

  $frontController->run();

  $frontController = new DataFrontController(array(
  "controller" => "product",
  "action"     => "select_specific",
  "params"     => array(array(
  'fields' => array('id_nove', 'nazev','bezna_cena' ),
  'conditions' => null,
  'tablename'=> array('thiago.wa_eshop_vyrobky'))
  )));
  $frontController->run();
 * 
  $frontController = new DataFrontController(array(
  "controller" => "product",
  "action"     => "select_specific",
  "params"     => array(array(
  'fields' => array('A.id_zakaznik', 'A.id', 'B.platba'),
  'conditions' => null,
  'tablename'=> array('thiago.wa_eshop_postovne', 'thiago.wa_eshop_platba'),
  'tablename_alias'=> array('A', 'B'),
  'join_type' => 'left',
  'join_condition' => 'A.id_zakaznik = B.id_zakaznik',
  'show_alias_header' => false)
  )));
  $frontController->run();
 */