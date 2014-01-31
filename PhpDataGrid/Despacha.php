<?php

//$controller = $_SESSION['control'];
//$action = $_SESSION['action'];

include './Loader.php';
if (isset($_GET) && count($_GET) > 0) {
    $controller = $_GET['control'];
    $action = $_GET['action'];
} else {
    $controller = $_POST['control'];
    $action = $_POST['action'];
}
switch ($controller) {
    case "product":
        switch ($action) {
            case "select":
                $search = $_GET['_search'];
                $val_order = $_GET['sidx'];
                $order = $_GET['sord'];
                $page = $_GET['page'];
                $row = $_GET['rows'];

                if ($search != "false") {

                    $frontController = new DataFrontController(array(
                        "controller" => $controller,
                        "action" => "desc_table",
                        "params" => array(null)
                    ));
                    $fields = $frontController->run();
                    $paramArray = array();
                    foreach ($fields as $key => $value) {
                        if (isset($_GET[$value]) && $_GET[$value] != null) {
                            $paramArray[$value] = $_GET[$value];
                        }
                    }
                    $frontController = new DataFrontController(array(
                        "controller" => $controller,
                        "action" => "filter",
                        "params" => array(array('val_order' => $val_order, 'order' => $order, 'conditions' => $paramArray, 'page' => $page, 'row' => $row))
                    ));
                    header('Content-Type: text/json');
                    echo rtrim($frontController->run());
                    break;
                } else {
                    $frontController = new DataFrontController(array(
                        "controller" => $controller,
                        "action" => $action,
                        "params" => array(array('val_order' => $val_order, 'order' => $order, 'conditions' => null, 'page' => $page, 'row' => $row))
                    ));
                    header('Content-Type: text/json');
                    echo rtrim($frontController->run());
                    break;
                }
            case "update":
                $param1 = $_POST['platba'];
                $param2 = $_POST['nazev_platba'];
                $param3 = $_POST['popis_platba'];
                $param4 = $_POST['sleva'];
                $condition = $_POST['condition'];
                $val_condition = $_POST['id'];
                $frontController = new DataFrontController(array(
                    "controller" => $controller,
                    "action" => $action,
                    "params" => array(array(
                            'tablename' => 'wa_eshop_platba',
                            'values' => array('platba' => $param1, 'nazev_platba' => $param2, 'popis_platba' => $param3, 'sleva' => $param4),
                            'condition_var' => $condition,
                            'condition_val' => $val_condition
                ))));
                echo rtrim($frontController->run());
                break;
            case "delete":
                $condition = $_POST['condition'];
                $val_condition = $_POST['id'];
                $frontController = new DataFrontController(array(
                    "controller" => $controller,
                    "action" => $action,
                    "params" => array(array(
                            'tablename' => 'wa_eshop_platba',
                            'condition_var' => $condition,
                            'condition_val' => $val_condition
                ))));
                echo rtrim($frontController->run());
                break;
            case "insert":
                $param1 = $_GET['platba'];
                $param2 = $_GET['nazev_platba'];
                $param3 = $_GET['popis_platba'];
                $param4 = $_GET['sleva'];
                $frontController = new DataFrontController(array(
                    "controller" => $controller,
                    "action" => $action,
                    "params" => array(array(
                            'tablename' => array('wa_eshop_platba'),
                            'values' => array(array('tablequery' => 0, 'platba' => $param1, 'nazev_platba' => $param2, 'popis_platba' => $param3, 'sleva' => $param4)))
                )));
                echo rtrim($frontController->run());
                break;
        }
        break;
    case "category":
        //TODO - Other categories
        break;
    case "customer":
        //TODO - Other categories
        break;
    case "characteristic":
        //TODO - Other categories
        break;
}
?>
