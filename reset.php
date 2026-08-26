<?php
session_start();

echo '<pre>';
echo $_SESSION['user']['role'];
exit;