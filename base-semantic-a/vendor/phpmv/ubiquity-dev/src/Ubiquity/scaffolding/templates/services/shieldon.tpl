// Notice that this directory must be writable.
$writable = \ROOT . \DS . 'cache' . \DS . 'shieldon';

// Initialize Firewall instance.
$firewall = new \Shieldon\Firewall($writable);
$firewall->run();
