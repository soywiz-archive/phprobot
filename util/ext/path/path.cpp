// Mad 5.0 - Path Finding class

// pathfind.cpp

#ifdef __cplusplus
extern "C" {
#endif

#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <memory.h>
#include "path.h"

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


PATHFIND::PATHFIND(short src_x, short src_y, short dst_x, short dst_y, char *walkarea, int width, int height) {
	long tmp, element = 0;
	char goal = 0;	

	pf_width  = width;
	pf_height = height;

	if (src_y < 0) src_y = 0; else if (src_y >= pf_height) src_y = pf_height - 1;
	if (src_x < 0) src_x = 0; else if (src_x >= pf_width)  src_x = pf_width - 1;	

	// allocate memory
	nodes = (NODE *)malloc(sizeof(NODE) * pf_width * pf_height);
	memset(nodes, 0, sizeof(NODE) * pf_width * pf_height);
	heap = (HEAP_NODE *)malloc(sizeof(HEAP_NODE) * pf_width * pf_height);
	y_table = (unsigned long *)malloc(sizeof(unsigned long) * pf_height);

	// get our y_table to avoid many multiplications
	for (int yt = 0, val = 0; yt < pf_height; yt++, val += pf_width) y_table[yt] = val;

	// transform the walkarea array to our nodes format	
	tmp = 0;
	for (int y = 0; y < pf_height; y++) {
		for (int x = 0; x < pf_width; x++, tmp++) {		
			//nodes[tmp].status = (getpixel(walkarea, x, y) == freecol) ? NODE_BLANK : NODE_BLOCKED;
			nodes[tmp].status = (walkarea[x + y * pf_width]) ? NODE_BLOCKED : NODE_BLANK;
		}
	}
  
	// check our src point
	if (nodes[y_table[src_y] + src_x].status == NODE_BLOCKED) {
		free(heap);
		error_code = PF_WRONGSRC;
		return;
	}

	// check our dst point
	if (nodes[y_table[dst_y] + dst_x].status == NODE_BLOCKED) {
		free(heap);
		error_code = PF_WRONGDST;
		return;
	}

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

	// free the allocated memory
	free(heap);
}

PATHFIND::~PATHFIND() {
	free(nodes);
	free(y_table);
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

#define DLL_EXPORT __declspec(dllexport)

DLL_EXPORT char *path_get(char *map_data, int map_w, int map_h, int x_src, int y_src, int x_dst, int y_dst, int time) {
	pos *pos_list; int pos_list_count = 0, n, p, d;
	pos myp;
	char *retval = "";
	
	PATHFIND *pf = new PATHFIND(x_dst, y_dst, x_src, y_src, map_data, map_w, map_h);

	if (pf->get_error() == PF_OK) {
		pos_list = (pos *)malloc(sizeof(pos) * 2500);	

		do {
			pf->get_actual_node(myp.x, myp.y);			
			pos_list[pos_list_count++] = myp;
		} while (pf->next_node() == PF_OK);

		retval = (char *)malloc((pos_list_count + 1) * 8);
		d = 0;

		for (n = 0; n < pos_list_count; n++) {
			p = pos_list_count - n - 1;			

			sprintf(&retval[d], "%i,%i\n", pos_list[p].x, pos_list[p].y);
			d += strlen(&retval[d]);			
		}
		retval[d] = 0;

		free(pos_list);
	}

	delete pf;

	return retval;
}

#ifdef __cplusplus
}
#endif
