<?php
	Import('System.Buffer');

	$DocumentAliases = array();

/*
<ul>
	<li /><k>Paquetes Recibidos</k>
	<ul>
		<li /><k>0x0069</k> - Login Correcto - Información
		<li /><k>0x006a</k> - Login Incorrecto
	</ul>
	<li /><k>Paquetes Enviados</k>
	<ul>
		<li /><k>0x0064</k> - Login Master Server
	</ul>
</ul>
*/
	$Packets       = array();
	$PacketsUpdate = array();

	function MakeDocumentationUpdate() {
		global $Packets;
		global $PacketsUpdate;

		$PacketsProc = array();

		foreach ($Packets as $cln => $cls) {
			$PacketsProc[$cln] = '';
			$k = &$PacketsProc[$cln];

			$k .= '<ul>';

			if (isset($cls['server'])) {
				$k .= '<li /><k>Paquetes Recibidos</k>';
				$k .= '<ul>';
				foreach ($cls['server'] as $pid => $pnm) {
					$k .= '<li />';
					$k .= '<k>0x' . str_pad(dechex($pid), 4, '0', STR_PAD_LEFT) . '</k> - ' . $pnm;
					// $pid, $pnm
				}
				$k .= '</ul>';
			}

			if (isset($cls['client'])) {
				$k .= '<li /><k>Paquetes Enviados</k>';
				$k .= '<ul>';
				foreach ($cls['client'] as $pid => $pnm) {
					$k .= '<li />';
					$k .= '<k>0x' . str_pad(dechex($pid), 4, '0', STR_PAD_LEFT) . '</k> - ' . $pnm;
					// $pid, $pnm
				}
				$k .= '</ul>';
			}

			$k .= '</ul>';
		}

		unset($k);

		//print_r($PacketsProc);

		foreach ($PacketsUpdate as $file) {
			//if (strpos($file, 'Zone') === false) continue;
			$data = file_get_contents($file);

			foreach ($PacketsProc as $k => $pp) {
				$data = str_ireplace('{packets_' . $k . '}', $pp, $data);
			}

			global $CurrentFile;
			$CurrentFile = $file;

			file_put_contents($file, MakeDocumentationFormat($data));
		}

		//print_r($PacketsProc);

		//print_r($Packets);
		//print_r($PacketsUpdate);
	}

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
			if (strpos($Data, '{packets_') !== false) {
				global $PacketsUpdate;
				$PacketsUpdate[] = $File;
			}
			fwrite($fd, $Data);
			fwrite($fd, MakeDocumentationGetFoot());
			fclose($fd);
		}
	}

	function MakeDocumentationParseParameters(SimpleXMLElement $Entries) {
		$Return = '<table border="1">';
		$Return .= '<tr>';
		$Return .= '<th>Nombre:</th>';
		$Return .= '<th>Tipo:</th>';
		$Return .= '<th>Long.:</th>';
		$Return .= '</tr>';
		foreach ($Entries as $k => $Entry) {
			if (strtolower($k) == 'entry') {
				$Type = SimpleXMLKeyValue($Entry, 'type');

				//if ($Type != 'function')

				$Return .= '<tr>';
				$Return .= '<td width="120" valign="top">';
				$Return .= SimpleXMLKeyValue($Entry, 'name');
				$Return .= '</td><td valign="top">';

				$Return .= $Type;

				$Return .= '</td><td valign="top">';

				//if (trim(strtolower($Type)) != 'function') {
				$Length = SimpleXMLKeyValue($Entry, 'length');

				if ($Length === false) {
					switch (trim(strtolower($Type))) {
						case 'uint8':  case 'int8':  $Length = 1; break;
						case 'uint16': case 'int16': $Length = 2; break;
						case 'pos24':                $Length = 3; break;
						case 'uint32': case 'int32': $Length = 4; break;
						case 'pos40':                $Length = 5; break;
					}
				}
				//}

				$Return .= $Length;

				if (trim(strtolower($Type)) == 'group') {
					$Return .= '<br /><br />';
					$Return .= MakeDocumentationParseParameters($Entry);
				}

				$Return .= '</td>';
				$Return .= '</tr>';
			}
		}
		$Return .= '</table>';
		return $Return;
	}

	function MakeDocumentationParsePacket(SimpleXMLElement $Entry, $Dir) {
		global $CurrentFile, $Packets;

		$IdHex       = '0x' . str_pad(dechex($Id = GetInteger(SimpleXMLKeyValue($Entry, 'id'))), 4, '0', STR_PAD_LEFT);
		$File        = $Dir . '/' . $IdHex . '.html';
		$CurrentFile = $File;
		$Length      = SimpleXMLKeyValue($Entry, 'length');
		$Server      = SimpleXMLKeyValue($Entry, 'server');
		$Sender      = SimpleXMLKeyValue($Entry, 'sender');
		$Description = SimpleXMLKeyValue($Entry, 'shortdescription');


		$Packets[trim(strtolower($Server))][trim(strtolower($Sender))][$Id] = $Description;

		$Data        = '';

		//$Data .= "<h2><k>{$IdHex}</k> - $Description</h2>";
		$Data .= "<h2>{$IdHex} - $Description</h2>";

		$Data .= '<p><b>Servidor: </b>';
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

		if (strtolower(trim($Sender)) == 'client') {
			$Data .= "<p><b>Env&iacute;a:</b> Cliente - Servidor (<k>Paquetes Enviados</k>)</p>";
		} else {
			$Data .= "<p><b>Env&iacute;a:</b> Servidor - Cliente (<k>Paquetes Recibidos</k>)</p>";
		}

		$Data .= '<p><b>Longitud del paquete: </b>';
		if ($Length == '-') {
			$Data .= '4 + Variable';
		} else {
			$Data .= '2 + ' . $Length;
		}
		$Data .= '</p>';

		$Data .= '<p><b>Par&aacute;metros:</b><br />';

		$Data .= MakeDocumentationParseParameters($Entry);
		// MakeDocumentationParseDocument

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
			fwrite($fd, MakeDocumentationGetHeader("{$IdHex} - " . $Description));
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

		MakeDocumentationUpdate();
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