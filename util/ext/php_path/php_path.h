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

#ifndef PHP_PATH_H
#define PHP_PATH_H

extern zend_module_entry path_module_entry;
#define phpext_path_ptr &path_module_entry

#ifdef PHP_WIN32
#define PHP_PATH_API __declspec(dllexport)
#else
#define PHP_PATH_API
#endif

//#define phpext_path_ptr NULL

PHP_MINIT_FUNCTION(path);
PHP_MSHUTDOWN_FUNCTION(path);
PHP_RINIT_FUNCTION(path);
PHP_RSHUTDOWN_FUNCTION(path);
PHP_MINFO_FUNCTION(path);

PHP_FUNCTION(path_find);

// general pathfinding return values
#define PF_OK 0            // no error
#define PF_ERROR -1        // general error
#define PF_NOWAY -2        // there were no path found
#define PF_WRONGSRC -3     // wrong (unwalkable) source point
#define PF_WRONGDST -4     // wrong (unwalkable) dest point

// next_node return values
#define PF_LASTNODE -5     // this is the last node (the dest point)

struct NODE {
	unsigned long f, g;
	unsigned short h;
	char status;
};

struct HEAP_NODE {
	short x, y;
};

class PATHFIND {
	private:
		NODE *nodes;
		HEAP_NODE *heap;
		unsigned long heapsize;
		unsigned short pf_width, pf_height;
		unsigned long *y_table, minf;
		long minh;
		short now_x, now_y, dest_x, dest_y;
		unsigned long opened_nodes;
		int error_code;
		unsigned short get_dist(short src_x, short src_y, short dst_x, short dst_y);
		void close_node(unsigned long element);
		void check_node(short x, short y, unsigned long g);
		void check_back_node(short x, short y,
		unsigned long &g, unsigned long &h,
		short &backx, short &backy);
		void insert_heap_element(short x, short y);
		void delete_heap_element(unsigned long element);
		unsigned long get_best_heap_element(void);

	public:
		PATHFIND::PATHFIND(short src_x, short src_y, short dst_x, short dst_y, char *walkarea, int width, int height, int block_values[13]);
		void get_actual_node(short &x, short &y);
		int next_node(void);
		int get_error(void);
		~PATHFIND();
};

#endif