<?php
//Internal
include 'config.php';
include 'internal/secu.php';


//Driver
foreach (glob("driver/*.php") as $filename)
{
    include $filename;
}

///Lib
foreach (glob("lib/*.php") as $filename)
{
    include $filename;
}

//Template
foreach (glob("template/*.php") as $filename)
{
    include $filename;
}