<?php
	Import('System.Buffer');

/*
	$xslt = <<<EOD
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
 <xsl:output method="html" encoding="iso-8859-1" indent="no"/>
 <xsl:template match="collection">
  Hey! Welcome to my sweet CD collection!
  <xsl:apply-templates/>
 </xsl:template>
 <xsl:template match="cd">
  <h1><xsl:value-of select="title"/></h1>
  <h2>by <xsl:value-of select="artist"/></h2>
  <h3> - <xsl:value-of select="year"/></h3>
 </xsl:template>
</xsl:stylesheet>
EOD;
*/

	$DocumentAliases = array();

	function MakeDocumentationParseDocument(SimpleXMLElement $Entry, $Dir) {
		global $DocumentAliases;

		$Name        = SimpleXMLKeyValue($Entry, 'name');
		$Description = SimpleXMLKeyValue($Entry, 'shortdescription');

		if (($a = SimpleXMLKeyValue($Entry, 'alias')) !== false) {
			$DocumentAliases[$Name] = $a;
			return;
		}

		$Data = ''; foreach ($Entry as $e1) {
			foreach ($e1 as $k => &$v) {
				$v = '<b>' . $v[0] . '</b>';
			}
			$Data .= $e1->asXML();
		}

		MakeDir($Dir);

		if ($fd = fopen($Dir . '/' . $Name . '.html', 'wb')) {
			fwrite($fd, '<html><head><title>' . $Description . '</title></head>');
			fwrite($fd, '<body>');
			fwrite($fd, $Data);
			fwrite($fd, '</body></html>');

			fclose($fd);
		}

		//echo "Document: $Name\n";
	}

	function MakeDocumentation($ProtocolVersion = 0x02, $Language = 'es') {
		global $DocumentAliases;

		$DocumentAliases = array();

		$Path = LUNEA_DATA . '/packets/';

		foreach (scandir($Path) as $FileName) {
			if (strcasecmp(substr($FileName, -4, 4), '.xml') == 0) {

				$o = simplexml_load_file($Path . $FileName);

				foreach ($o->attributes() as $k => $v) {
					switch (strtolower(trim($k))) {
						case 'protocolversion':
							$ProtocolVersion2 = GetInteger($v);
						break;
					}
				}

				if (!isset($ProtocolVersion2) || $ProtocolVersion != $ProtocolVersion2) break;

				foreach ($o as $k => $Entry) {
					switch (strtolower($k)) {
						case 'document':
							if (SimpleXMLKeyValue($Entry, 'language') == $Language) {
								MakeDocumentationParseDocument($Entry, LUNEA_DOCS . '/packets/' . $Language);
							}
						break;
					}
				}

			}
		}
	}

	function SimpleXMLKeyValue(SimpleXMLElement $entry, $key) {
		$key = trim($key);
		foreach ($entry->attributes() as $an => $av) {
			if (strcasecmp($an, $key) == 0) return (string)$av;
		}
		return false;
	}

	function SimpleXMLGetChildrenAsXML(SimpleXMLElement $entry) {
		$r = ''; foreach ($entry->children() as $c) $r .= $c->asXML();
		return $r;
	}

	function MakeDir($dir) {
		$dir = str_replace('\\', '/', $dir);
		$rdir = '';
		foreach (explode('/', $dir) as $cdir) {
			if (!strlen($cdir)) continue;

			$rdir .= $cdir . '/';
			if (!is_dir($rdir)) @mkdir($rdir, 0777);
		}
	}

	MakeDocumentation();

	print_r($DocumentAliases);
?>