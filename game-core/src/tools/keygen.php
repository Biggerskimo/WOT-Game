<?php
$bytes = isset($_REQUEST['bytes']) ? intval($_REQUEST['bits']) : mcrypt_enc_get_key_size(mcrypt_module_open(MCRYPT_RIJNDAEL_192, '', MCRYPT_MODE_CFB, ''));
$bits = $bytes << 3;
$key = str_pad("0", $bytes);

$randBytes = ((int)min(floor(log(mt_getrandmax(), 2)), 0x7FFFFFFF)) >> 3;
$randBits = $randBytes << 3;

echo "Schlüssellänge: ".$bits."<br />\n";
echo "Länge eines mt_rand()-Aufrufs: ".$randBits."<br />\n";

for($doneBytes = 0; $doneBytes < $bytes; $doneBytes += $randBytes) {
	$rand = mt_rand(0, pow(2, $randBits));
	
	for($i = 0; $i < $randBytes; $i++) {
		$key[$doneBytes + $i] = chr(($rand >> ($i << 3)) & 0xFF);
	}
}
echo "Binärschlüssel: \"".htmlspecialchars($key)."\"<br />\n";
echo "base64: \"".base64_encode($key)."\"<br />\n";
?>