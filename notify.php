<?php
$d = new Dbus( Dbus::BUS_SESSION );
$n = $d->createProxy(
    "org.freedesktop.Notifications", // connection name
    "/org/freedesktop/Notifications", // object
    "org.freedesktop.Notifications" // interface
);
$id = $n->Notify(
    'Testapp', new DBusUInt32( 0 ), // app_name, replaces_id
    'iceweasel', 'Testing http://ez.no', 'Test Notification', // app_icon, summary, body
    new DBusArray( DBus::STRING, array() ), // actions
    new DBusDict(                         // hints
        DBus::VARIANT,
        array(
            'x' => new DBusVariant( 500 ), // x position on screen
            'y' => new DBusVariant( 500 ), // y position on screen
            'desktop-entry' => new DBusVariant( 'rhythmbox' )
        )
    ),
    1000 // expire timeout in msec
);
echo $id[0], "\n";
?>
