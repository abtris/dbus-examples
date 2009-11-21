<?php
$d = new Dbus;
$n = $d->createProxy(
        "org.gnome.ScreenSaver",
            "/org/gnome/ScreenSaver",
                "org.gnome.ScreenSaver"
            );
var_dump($n->GetActive());
$n->SetActive( true );
var_dump($n->GetActive());
sleep(5);
$n->SetActive( false );
?>

