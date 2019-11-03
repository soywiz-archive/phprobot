#ifndef _PHP_MAP_H
#define _PHP_MAP_H
	extern zend_module_entry map_module_entry;
	#define map_module_ptr &map_module_entry

#ifdef PHP_WIN32
#define PHP_MAP_API __declspec(dllexport)
#else
#define PHP_MAP_API
#endif

	struct Map {
		void *map;
		int width, height;
	};
	
	typedef struct Map* Mapptr;

	PHP_RINIT_FUNCTION(Map);
	PHP_MINIT_FUNCTION(Map);
	PHP_MINFO_FUNCTION(Map);

static ZEND_BEGIN_ARG_INFO(map__set_arg_info, 0)
       	ZEND_ARG_INFO(0, variable)
      	ZEND_ARG_INFO(0, value)
		ZEND_END_ARG_INFO();

static ZEND_BEGIN_ARG_INFO(map__get_arg_info, 0)
       	ZEND_ARG_INFO(0, variable)
		ZEND_END_ARG_INFO();

	PHP_METHOD(Map, __construct);
	PHP_METHOD(Map, Update);
	PHP_METHOD(Map, __set);
	//PHP_METHOD(Map, __get);
	PHP_METHOD(Map, Get);
	PHP_METHOD(Map, Put);
	PHP_METHOD(Map, Find);
#else
	#define map_module_ptr NULL
#endif

#define phpext_map_ptr map_module_ptr