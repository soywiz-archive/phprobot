#ifndef _PHP_MAP_H
#define _PHP_MAP_H
	extern zend_module_entry map_module_entry;
	#definemap_module_ptr &map_module_entry

#ifdef PHP_WIN32
#define PHP_MAP_API __declspec(dllexport)
#else
#define PHP_MAP_API
#endif

	typedef Map* Mapptr;


	PHP_RINIT_FUNCTION(Map);
	PHP_MINIT_FUNCTION(Map);
	PHP_MINFO_FUNCTION(Map);

	PHP_METHOD(Map, __construct);
	PHP_METHOD(Map, __set);
	PHP_METHOD(Map, Get);
	PHP_METHOD(Map, Put);
	PHP_METHOD(Map, Find);
#else
	#define map_module_ptr NULL
#endif

#define phpext_map_ptr map_module_ptr