/*
   +----------------------------------------------------------------------+
   | PHP Version 5                                                        |
   +----------------------------------------------------------------------+
   | Copyright (c) 1997-2004 The PHP Group                                |
   +----------------------------------------------------------------------+
   | This source file is subject to version 3.0 of the PHP license,       |
   | that is bundled with this package in the file LICENSE, and is        |
   | available through the world-wide-web at the following url:           |
   | http://www.php.net/license/3_0.txt.                                  |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy impathtely.                |
   +----------------------------------------------------------------------+
   | Author: Carlos Ballesteros Velasco <soywiz@gmail.com>                |
   +----------------------------------------------------------------------+
 */

#pragma warning(disable: 4127)
#pragma warning(disable: 4244)
#pragma warning(disable: 4100)
#pragma warning(disable: 4101)
#pragma warning(disable: 4189)
#pragma warning(disable: 4505)
#pragma warning(disable: 4701)
#pragma warning(disable: 4702)

#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <memory.h>

#include "php.h"
#include "php_ini.h"
#include "php_path.h"
#include "SAPI.h"
#include "ext/standard/info.h"

//#undef printf
//#define printf if (0)

/*
#ifdef __cplusplus
extern "C" {
#endif
*/

// internal defines
#define NODE_BLANK 0
#define NODE_BLOCKED 1
#define NODE_OPENED 2
#define NODE_CLOSED 3

void PATHFIND::check_node(short x, short y, unsigned long g) {
	long tmp = y_table[y] + x;

	if ((x < 0 || x >= pf_width) || ((y < 0) || (y >= pf_height))) return;

	if (nodes[tmp].status == NODE_BLANK) {
		nodes[tmp].status = NODE_OPENED;
		opened_nodes++;
		nodes[tmp].g = g;
		nodes[tmp].h = get_dist(x, y, now_x, now_y);
		nodes[tmp].f = g + nodes[tmp].h;
		insert_heap_element(x, y);
	} else if ((nodes[tmp].status == NODE_OPENED) && (nodes[tmp].g > g)) {
		nodes[tmp].g = g;
		nodes[tmp].f = g + nodes[tmp].h;
	}
}

void PATHFIND::close_node(unsigned long element) {
	short lastx = heap[element].x, lasty = heap[element].y;
	unsigned long lastg = nodes[y_table[lasty] + lastx].g + 1;
	nodes[y_table[lasty] + lastx].status = NODE_CLOSED;
	delete_heap_element(element);
	opened_nodes--;

	// Se debe borrar?
	///*
	if ((lasty > 0) && (lastx > 0)) check_node(lastx - 1, lasty - 1, lastg);
	if ((lasty > 0) && (lastx < (pf_width - 1))) check_node(lastx + 1, lasty - 1, lastg);
	if ((lasty < (pf_height - 1)) && (lastx > 0)) check_node(lastx - 1, lasty + 1, lastg);
	if ((lasty < (pf_height - 1)) && (lastx < (pf_width - 1))) check_node(lastx + 1, lasty + 1, lastg);
	//*/

	if (lasty > 0) check_node(lastx, lasty - 1, lastg);
	if (lasty < (pf_height - 1)) check_node(lastx, lasty + 1, lastg);
	if (lastx > 0) check_node(lastx - 1, lasty, lastg);
	if (lastx < (pf_width - 1)) check_node(lastx + 1, lasty, lastg);
}

void PATHFIND::insert_heap_element(short x, short y) {
	if ((x < 0 || x >= pf_width) || ((y < 0) || (y >= pf_height))) return;

	heap[heapsize].x = x;
	heap[heapsize].y = y;
	heapsize++;
}

void PATHFIND::delete_heap_element(unsigned long element) {
	unsigned long i;
	heapsize--;
	for (i = element; i < heapsize; i++) {
		heap[i].x = heap[i + 1].x;
		heap[i].y = heap[i + 1].y;
	}
}

unsigned long PATHFIND::get_best_heap_element(void) {
	long tmp_minh = minh;
	unsigned long tmp_minf = minf;
	unsigned long i, j, element = 0;

	for (i = 0; i < heapsize; i++) {
		j = y_table[heap[i].y] + heap[i].x;
		if (nodes[j].f <= tmp_minf) {
			if (nodes[j].h < tmp_minh) {
				element = i;
				tmp_minf = nodes[j].f;
				tmp_minh = nodes[j].h;
			}
		}
	}

	return element;
}


PATHFIND::PATHFIND(short src_x, short src_y, short dst_x, short dst_y, char *walkarea, int width, int height, int block_values[13]) {
	long tmp, element = 0;
	char goal = 0;

	if (width < 0 || height < 0) { error_code = PF_WRONGSRC; return; }

	//printf("[2]");

	pf_width  = width;
	pf_height = height;

	if ((src_x < 0) || (src_x >= pf_height)) { error_code = PF_WRONGSRC; return; }
	if ((src_y < 0) || (src_y >= pf_height)) { error_code = PF_WRONGSRC; return; }	

	if ((dst_x < 0) || (dst_x >= pf_width))  { error_code = PF_WRONGDST; return; }	
	if ((dst_y < 0) || (dst_y >= pf_width))  { error_code = PF_WRONGDST; return; }			

	//printf("[/2]");

	//printf("[3]");

	// allocate memory
	nodes = (NODE *)emalloc(sizeof(NODE) * pf_width * pf_height);
	memset(nodes, 0, sizeof(NODE) * pf_width * pf_height);
	heap = (HEAP_NODE *)emalloc(sizeof(HEAP_NODE) * pf_width * pf_height);
	y_table = (unsigned long *)emalloc(sizeof(unsigned long) * pf_height);

	//printf("[/3]");

	//printf("[4]");

	// get our y_table to avoid many multiplications
	for (int yt = 0, val = 0; yt < pf_height; yt++, val += pf_width) y_table[yt] = val;

	// transform the walkarea array to our nodes format	
	tmp = 0;
	int z = 0;

	for (int y = 0; y < pf_height; y++) {
		for (int x = 0; x < pf_width; x++, tmp++) {
			//nodes[tmp].status = (getpixel(walkarea, x, y) == efreecol) ? NODE_BLANK : NODE_BLOCKED;			
			nodes[tmp].status = ((walkarea[tmp] == 1) || (walkarea[tmp] == 5)) ? NODE_BLOCKED : NODE_BLANK;
			
			z = NODE_BLANK;
			for (int n = 0; n < 12; n++) {
				if (block_values[n] == -1) break;
				if (block_values[n] == walkarea[tmp]) { z = NODE_BLOCKED; break; }
			}

			nodes[tmp].status = z;
		}
	}

	//printf("[/4]");

	//printf("[5]");

	// check our src point
	//printf("[-1-]");
	if (nodes[y_table[src_y] + src_x].status == NODE_BLOCKED) {
		//printf("[X]");
		efree(heap);
		error_code = PF_WRONGSRC;
		return;
	}

	//printf("ST: [%i, %i]", (int)dst_x, (int)dst_y);

	// check our dst point
	if (nodes[y_table[dst_y] + dst_x].status == NODE_BLOCKED) {
		//printf("[X]");
		efree(heap);
		error_code = PF_WRONGDST;
		return;
	}

	//printf("[/5]");

	// we initialize some variables
	error_code = PF_OK;
	heapsize = 0;
	opened_nodes = 0;
	dest_x = dst_x; dest_y = dst_y;
	now_x = src_x; now_y = src_y;
	insert_heap_element(dst_x, dst_y);
	minh = pf_width * pf_height;
	minf = minh << 1;

	// and until we have reached our goal
	while (!goal) {
		// have we reached our goal?
		if ((heap[element].x == src_x) && (heap[element].y == src_y)) {
			// yes, close and exit from this while
			close_node(element);
			goal = -1;
			continue;
		}

		// close the node
		close_node(element);

		// do we still have a way to check?
		if (opened_nodes == 0) {
			error_code = PF_NOWAY;
			break;
		}

		// now let's check wich opened node is the best
		element = get_best_heap_element();
	}

	now_x = src_x;
	now_y = src_y;

	// efree the allocated memory
	efree(heap);
}

PATHFIND::~PATHFIND() {
	efree(nodes);
	efree(y_table);
}

void PATHFIND::get_actual_node(short &x, short &y) {
	x = now_x;
	y = now_y;
}

void PATHFIND::check_back_node(short x, short y, unsigned long &g, unsigned long &h, short &backx, short &backy) {
	if ((x < 0 || x >= pf_width) || ((y < 0) || (y >= pf_height))) return;

	long tmp;
	tmp = y_table[y] + x;
	if (tmp < 0) return;

	if (nodes[tmp].status == NODE_CLOSED) {
		if (nodes[tmp].g <= g) {
			if (nodes[tmp].h < (signed)h) {
				backx = x;
				backy = y;
				g = nodes[tmp].g;
				h = nodes[tmp].h;
			}
		}
	}
}

int PATHFIND::next_node(void) {
	if (error_code != PF_OK) return PF_ERROR;
	if ((now_x == dest_x) && (now_y == dest_y)) return PF_LASTNODE;

   	unsigned long tmp_minh = minh;
	unsigned long tmpg = nodes[y_table[now_y] + now_x].g - 1;
	short x, y;

	///*
	if ((now_y > 0) && (now_x > 0)) check_back_node(now_x - 1, now_y - 1, tmpg, tmp_minh, x, y); // up-left
	if ((now_y > 0) && (now_x < (pf_width - 1))) check_back_node(now_x + 1, now_y - 1, tmpg, tmp_minh, x, y); // up-right
	if ((now_y < (pf_height - 1)) && (now_x > 0)) check_back_node(now_x - 1, now_y + 1, tmpg, tmp_minh, x, y); // down-left
	if ((now_y < (pf_height - 1)) && (now_x < (pf_width - 1))) check_back_node(now_x + 1, now_y + 1, tmpg, tmp_minh, x, y); // down-right
	//*/

	if (now_y > 0) check_back_node(now_x, now_y - 1, tmpg, tmp_minh, x, y); // up
	if (now_y < (pf_height - 1)) check_back_node(now_x, now_y + 1, tmpg, tmp_minh, x, y); // down
	if (now_x > 0) check_back_node(now_x - 1, now_y, tmpg, tmp_minh, x, y); // left
	if (now_x < (pf_width - 1)) check_back_node(now_x + 1, now_y, tmpg, tmp_minh, x, y); // right

	now_x = x;
	now_y = y;

	return PF_OK;
}

unsigned short PATHFIND::get_dist(short src_x, short src_y, short dst_x, short dst_y) {
	unsigned short tmp1, tmp2;
	tmp1 = abs(dst_x - src_x);
	tmp2 = abs(dst_y - src_y);
	return tmp1 + tmp2;
}

int PATHFIND::get_error(void) {
	return error_code;
}

/////////////////////////////////////////////
/////////////////////////////////////////////

// DLL_EXPORT

struct pos {
	short x;
	short y;
};

function_entry path_functions[] = {
	PHP_FE(path_find,	NULL)
	{NULL, NULL, NULL}	/* Must be the last line in path_functions[] */
};

zend_module_entry path_module_entry = {
	STANDARD_MODULE_HEADER,
	"path",
	path_functions,
	NULL,
	NULL,
	NULL,
	NULL,
	PHP_MINFO(path),
    NO_VERSION_YET,
	STANDARD_MODULE_PROPERTIES
};

ZEND_GET_MODULE(path)

PHP_MINFO_FUNCTION(path) {
	php_info_print_table_start();
	php_info_print_table_row(2, "Path functions", "enabled");
	php_info_print_table_end();
}

PHP_FUNCTION(path_find) {
	zval **_map_data, **_map_w, **_map_h, **_x_src, **_y_src, **_x_dst, **_y_dst, **_b_list;
	zval **element;
	char *map_data;
	int map_w, map_h, x_src, y_src, x_dst, y_dst;	
	int block_values[12] = { 1, 5, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1 };
		
	switch (ZEND_NUM_ARGS()) {
		case 7:
			if ((zend_get_parameters_ex(7, &_map_data, &_map_w, &_map_h, &_x_src, &_y_src, &_x_dst, &_y_dst) == FAILURE)) WRONG_PARAM_COUNT;
		break;
		case 8: // IDs de bloque
			if ((zend_get_parameters_ex(8, &_map_data, &_map_w, &_map_h, &_x_src, &_y_src, &_x_dst, &_y_dst, &_b_list) == FAILURE)) WRONG_PARAM_COUNT;
			convert_to_array_ex(_b_list);

			block_values[0] = -1;
			block_values[1] = -1;

			int n = 0;
			for (zend_hash_internal_pointer_reset(Z_ARRVAL_PP(_b_list));
				 zend_hash_get_current_data(Z_ARRVAL_PP(_b_list), (void **) &element) == SUCCESS;
				 zend_hash_move_forward(Z_ARRVAL_PP(_b_list))) {
				convert_to_long_ex(element);

				block_values[n++] = Z_LVAL_PP(element);
				if (n == 12) break;
			}
		break;
	}

	//Z_ARRVAL_PP	

	convert_to_string_ex(_map_data); map_data = Z_STRVAL_PP(_map_data);
	convert_to_long_ex(_map_w);      map_w    = Z_LVAL_PP(_map_w);
	convert_to_long_ex(_map_h);      map_h    = Z_LVAL_PP(_map_h);
	convert_to_long_ex(_x_src);      x_src    = Z_LVAL_PP(_x_src);
	convert_to_long_ex(_y_src);      y_src    = Z_LVAL_PP(_y_src);
	convert_to_long_ex(_x_dst);      x_dst    = Z_LVAL_PP(_x_dst);
	convert_to_long_ex(_y_dst);	     y_dst    = Z_LVAL_PP(_y_dst);

	// El mapa no tiene las dimensiones deseadas
	if (Z_STRLEN_PP(_map_data) < (map_w * map_h)) RETURN_FALSE;
	
	PATHFIND *pf = new PATHFIND(x_dst, y_dst, x_src, y_src, map_data, map_w, map_h, block_values);

	if (pf->get_error() == PF_OK) {
		array_init(return_value);
		zval *array_pose;

		do {
			short mx, my;

			pf->get_actual_node(mx, my);

			MAKE_STD_ZVAL(array_pose);
			array_init(array_pose);	
			add_next_index_long(array_pose, mx); // x
			add_next_index_long(array_pose, my); // y
			add_next_index_zval(return_value, array_pose);
		} while (pf->next_node() == PF_OK);

		if (pf) delete pf;
	} else {
		RETVAL_FALSE;
	}
}

/*
#ifdef __cplusplus
}
#endif
*/