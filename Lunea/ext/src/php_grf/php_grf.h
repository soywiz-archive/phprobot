#ifndef _PHP_GRF_H
#define _PHP_GRF_H
	extern zend_module_entry grf_module_entry;
	#define grf_module_ptr &grf_module_entry

#ifdef PHP_WIN32
#define PHP_GRF_API __declspec(dllexport)
#else
#define PHP_GRF_API
#endif
	#include "grf\grf.h"

	typedef Grf* Grfptr;


	PHP_RINIT_FUNCTION(Grf);
	PHP_MINIT_FUNCTION(Grf);
	PHP_MINFO_FUNCTION(Grf);

	PHP_METHOD(Grf, __construct);
	PHP_METHOD(Grf, Find);
	PHP_METHOD(Grf, Read);
	PHP_METHOD(Grf, ReadIndex);
#else
	#define grf_module_ptr NULL
#endif

#define phpext_grf_ptr grf_module_ptr