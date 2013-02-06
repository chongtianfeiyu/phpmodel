<?php
namespace factory;
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of FactoryException
 *
 * @author Administrator
 */
class FactoryException extends \Exception {
    
    public function __construct($message, $code, $previous) {
        parent::__construct($message, $code, $previous);
    }
    //put your code here
}

?>
