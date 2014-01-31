<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 *
 * @author User
 */
interface DataInterface {
    public function setController($controller);
    public function setAction($action);
    public function setParams(array $params);
    public function run();
}
