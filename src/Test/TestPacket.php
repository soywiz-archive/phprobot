<?php
	//Import('Packets.*');
	Import('Packets.PacketList');

	print_r(PacketList::LoadFromFile(0x02));
?>