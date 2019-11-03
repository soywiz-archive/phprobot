#ifndef PATHFIND_H
#define PATHFIND_H

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
		PATHFIND::PATHFIND(short src_x, short src_y, short dst_x, short dst_y, char *walkarea, int width, int height, int type);
		void get_actual_node(short &x, short &y);
		int next_node(void);
		int get_error(void);
		~PATHFIND();
};

/*
struct pos {
	short x;
	short y;
};
*/

//#define DLL_EXPORT

//DLL_EXPORT char *path_get(char *map_data, int map_w, int map_h, int x_src, int y_src, int x_dst, int y_dst, int time);

#define FIND_WALK 0
#define FIND_FLY 1

#endif