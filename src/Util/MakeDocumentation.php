<?php
	Import('System.Buffer');

	$DocumentAliases = array();

	function PregReplaceForLinkAliase($Match) {
		global $CurrentFile;
		global $DocumentAliases;

		$Key = $Match[2];

		while (isset($DocumentAliases[$Key])) {
			if (basename($CurrentFile) == $Key . '.html') {
				return '<font color=red><b>' . $Match[2] . '</b></font>';
			}
			$Key = $DocumentAliases[$Key];
		}

		if (basename($CurrentFile) == $Key . '.html') {
			return '<font color=red><b>' . $Match[2] . '</b></font>';
		} else {
			return '<a href="' . str_replace(' ', '%20', $Key) . '.html">' . $Match[2] . '</a>';
		}
	}

	function MakeDocumentationGetHeader($Description) {
		return '<html><head><link rel="stylesheet" href="../documentation.css"/><title>' . $Description . '</title><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /></head><body>';
	}

	function MakeDocumentationGetFoot() {
		return '</body></html>';
	}

	function MakeDocumentationFormat($Data) {
		return preg_replace_callback('/(<k>)([^<]*)(<\\/k>)/i', 'PregReplaceForLinkAliase', $Data);
	}

	function MakeDocumentationParseDocument(SimpleXMLElement $Entry, $Dir) {
		global $DocumentAliases;

		$Name        = SimpleXMLKeyValue($Entry, 'name');
		$Description = SimpleXMLKeyValue($Entry, 'shortdescription');

		if (($a = SimpleXMLKeyValue($Entry, 'alias')) !== false) {
			$DocumentAliases[$Name] = $a;
			return;
		}

		MakeDir($Dir);
		$File = $Dir . '/' . $Name . '.html';

		global $CurrentFile;

		$CurrentFile = $File;

		$Data = MakeDocumentationFormat(SimpleXMLGetChildrenAsXML($Entry));

		if ($fd = fopen($File, 'wb')) {
			fwrite($fd, MakeDocumentationGetHeader($Description));
			fwrite($fd, $Data);
			fwrite($fd, MakeDocumentationGetFoot());
			fclose($fd);
		}
	}

	function MakeDocumentationParsePacket(SimpleXMLElement $Entry, $Dir) {
		global $CurrentFile;

		$IdHex       = '0x' . str_pad(dechex(GetInteger(SimpleXMLKeyValue($Entry, 'id'))), 4, '0', STR_PAD_LEFT);
		$File        = $Dir . '/' . $IdHex . '.html';
		$CurrentFile = $File;
		$Length      = SimpleXMLKeyValue($Entry, 'id');
		$Server      = SimpleXMLKeyValue($Entry, 'server');
		$Sender      = SimpleXMLKeyValue($Entry, 'sender');
		$Description = SimpleXMLKeyValue($Entry, 'shortdescription');
		$Data        = '';

		//$Data .= "<h2><k>{$IdHex}</k> - $Description</h2>";
		$Data .= "<h2>{$IdHex} - $Description</h2>";
		if (strtolower(trim($Sender)) == 'client') {
			$Data .= "<p><b>Env&iacute;a:</b> Cliente - Servidor (<k>Paquetes Enviados</k>)</p>";
		} else {
			$Data .= "<p><b>Env&iacute;a:</b> Servidor - Cliente (<k>Paquetes Recibidos</k>)</p>";
		}

		$Data .= '<p><b>Par&aacute;metros:</b><br />';

		$Data .= '</p>';

		$Data .= '<p><b>Notas:</b><br />';

		$Data .= '</p>';

		$Data .= '<hr /><p>Volver a: <k>Principal</k> ';
		switch (strtolower(trim($Server))) {
			case 'master':
				$Data .= '<k>Master</k>';
			break;
			case 'chara':
				$Data .= '<k>Character</k>';
			break;
			case 'zone':
				$Data .= '<k>Zone</k>';
			break;
		}
		$Data .= '</p>';

		$Data = MakeDocumentationFormat($Data);

		if ($fd = fopen($File, 'wb')) {
			fwrite($fd, MakeDocumentationGetHeader($Description));
			fwrite($fd, $Data);
			fwrite($fd, MakeDocumentationGetFoot());
			fclose($fd);
		}
	}

	function MakeDocumentation($ProtocolVersion = 0x06, $Language = 'es') {
		global $DocumentAliases;

		$DocumentAliases = array();

		$Path     = LUNEA_DATA . '/packets/';
		$PathHtml = LUNEA_DOCS . '/packets/' . $Language;

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
								MakeDocumentationParseDocument($Entry, $PathHtml);
							}
						break;
						case 'packet':
							MakeDocumentationParsePacket($Entry, $PathHtml);
							//echo GetInteger(SimpleXMLKeyValue($Entry, 'id')) . "\n";
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