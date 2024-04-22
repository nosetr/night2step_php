<?php

/**
 * Acl.php
 * Description of Acl
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 13.09.2012 14:50:20
 * 
 */
class N2S_Auth_Acl extends Zend_Acl
{
    public function __construct()
    {
        // RESSOURCES DEFAULT
        $this->add(new Zend_Acl_Resource('default'));

        // RESSOURCES COMMUNITY
        $this->add(new Zend_Acl_Resource('community'));
        
        // RESSOURCES ADMINISTRATION
        $this->add(new Zend_Acl_Resource('adminka'));
        //$this->add(new Zend_Acl_Resource('friends'), 'community');

        //ROLES
        $this->addRole(new Zend_Acl_Role('guest')); 
        $this->addRole(new Zend_Acl_Role('Registered'), 'guest');
        $this->addRole(new Zend_Acl_Role('Administrator'), 'Registered');
        $this->addRole(new Zend_Acl_Role('Super Administrator'), 'Administrator');

        //FOR GUEST DENY
        $this->deny('guest', 'community');
        //FOR GUESTS ALLOW
        $this->allow('guest', 'default');
        $this->allow('guest', 'community', 'index');

        //FOR MEMBERS ALLOW
        $this->allow('Registered', 'community');

        //FOR SUPER ADMINS ALLOW
        $this->allow('Super Administrator', 'adminka');
    }
}