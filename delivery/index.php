<?php

require_once 'Classes/Admin.php';
require_once 'Classes/User.php';

 use DELIVERY\Classes\Admin\Admin;


 $admin = new Admin("rindra.it@gmail.com", "rindra", "Rindra Razafinjatovo");

//  $admin->createUser("rindra.it@gmail.com", "rindra", "Rindra Razafinjatovo");

echo $admin->login("rindra.it@gmail.com", "rindra");