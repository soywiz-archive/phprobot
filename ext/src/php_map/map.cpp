#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include "php.h"
#include "php_globals.h"
#include "ext/standard/info.h"

#include "php_map.h"
#include "Path/path.h"

static zend_class_entry *map_class_entry_ptr;

static zend_function_entry map_functions[] = {
	{ NULL, NULL, NULL }
};

static zend_function_entry map_class_functions[] = {
	PHP_ME(Map, __construct,      NULL, 0)
	PHP_ME(Map, __set,            map__set_arg_info, 0)
	//PHP_ME(Map, __get,            map__get_arg_info, 0)
	PHP_ME(Map, Get,              NULL, 0)
	PHP_ME(Map, Put,              NULL, 0)
	PHP_ME(Map, Find,             NULL, 0)
	{ NULL, NULL, NULL }
};

zend_module_entry map_module_entry = {
	STANDARD_MODULE_HEADER,
	"Map",
	map_functions,
	PHP_MINIT(Map), /* module init function */
	NULL,           /* module shutdown function */
	PHP_RINIT(Map), /* request init function */
	NULL,           /* request shutdown function */
	PHP_MINFO(Map), /* module info function */
	NO_VERSION_YET,
	STANDARD_MODULE_PROPERTIES
};

ZEND_GET_MODULE(map)

/*
static void *PHPgetProperty(zval *id, char *name, int namelen, int proptype TSRMLS_DC) {
	zval **tmp;
	int id_to_find;
	void *property;
	int type;

	if (id) {
		if (zend_hash_find(Z_OBJPROP_P(id), name, namelen + 1, (void **)&tmp) == FAILURE) {
			php_error_docref(NULL TSRMLS_CC, E_WARNING, "Unable to find property %s", name);
			return NULL;
		}
		id_to_find = Z_LVAL_PP(tmp);
	} else {
		return NULL;
	}

	property = zend_list_find(id_to_find, &type);

	if (!property || type != proptype) {
		php_error_docref(NULL TSRMLS_CC, E_WARNING, "Unable to find identifier (%d)", id_to_find);
		return NULL;
	}

	return property;
}


static Mapptr getMap(zval *id TSRMLS_DC){
	void *map = PHPgetProperty(id, "map", 3, le_mapp TSRMLS_CC);

	if (!map) { php_error_docref(NULL TSRMLS_CC, E_ERROR, "Called object is not an Map"); }
	return (Mapptr)map;
}
*/


PHP_METHOD(Map, __construct) {	
	zval **width, **height, **data;

	//if (ZEND_NUM_ARGS() != 1 || zend_get_parameters_ex(1, &filename) == FAILURE) WRONG_PARAM_COUNT;	
	if (ZEND_NUM_ARGS() != 3 || zend_get_parameters_ex(3, &data, &width, &height) == FAILURE) WRONG_PARAM_COUNT;	

	convert_to_string_ex(data);
	convert_to_long_ex(width);
	convert_to_long_ex(height);
	//mygrf = grf_callback_open(Z_STRVAL_PP(filename), "rb", NULL, NULL);

	
	//mymap = (Mapptr)emalloc(sizeof(Map));

	// Posiblemente PHP borre la variable despues
	//mymap->map    = (void *)data;
	//mymap->map    = zend_str_dup(data, );
	//mymap->width  = Z_LVAL_PP(width);
	//mymap->height = Z_LVAL_PP(height);

	//ret = zend_list_insert(mymap, le_mapp);
	
	object_init_ex(getThis(), map_class_entry_ptr);
	//add_property_resource(getThis(), "map", ret);
	add_property_zval(getThis(), "data", *data);
	add_property_long(getThis(), "width", Z_LVAL_PP(width));
	add_property_long(getThis(), "height", Z_LVAL_PP(height));
	//zend_list_addref(ret);

	//RETURN_FALSE;
}

PHP_METHOD(Map, __set) {
	zval *val, *tmp;
	char *var;
	int var_l;
	//Mapptr mymap;

	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "sz", &var, &var_l, &val) == FAILURE) WRONG_PARAM_COUNT;

	if (zend_hash_find(Z_OBJPROP_P(getThis()), var, var_l, (void **)&tmp) == SUCCESS ) {
		//zend_hash_index_update(Z_OBJPROP_P(getThis()), Z_LVAL_P(tmp), (void *)&val, sizeof(zval *), NULL);
	} else {
		add_property_zval(getThis(), var, val);
	}

	//if ((mymap = getMap(getThis() TSRMLS_CC)) != NULL ) {
		if (strcasecmp(var, "width") == 0) {
			//convert_to_double(val);
			//fann_set_learning_rate(ann, Z_DVAL_P(val));
			//printf("Update width\n");
		} else if (strcasecmp(var, "height") == 0) {
			//convert_to_long(val);
			//fann_set_activation_function_hidden(ann, Z_LVAL_P(val));
			//printf("Update Height\n");
		}
	//}
}

PHP_METHOD(Map, Get) {
	RETURN_FALSE;
}

PHP_METHOD(Map, Put) {
	RETURN_FALSE;
}

PHP_METHOD(Map, Find) {
	zval *width, *height, *data;
	int x_src, y_src, x_dst, y_dst, type;

	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "lllll", &x_src, &y_src, &x_dst, &y_dst, &type) == FAILURE) WRONG_PARAM_COUNT;	

	width  = zend_read_property(map_class_entry_ptr, getThis(), "width",  5, 1 TSRMLS_CC);
	height = zend_read_property(map_class_entry_ptr, getThis(), "height", 6, 1 TSRMLS_CC);
	data   = zend_read_property(map_class_entry_ptr, getThis(), "data",   4, 1 TSRMLS_CC);

	if (Z_STRLEN_P(data) >= Z_LVAL_P(width) * Z_LVAL_P(height)) {
		PATHFIND *pf = new PATHFIND(x_dst, y_dst, x_src, y_src, Z_STRVAL_P(data), Z_LVAL_P(width), Z_LVAL_P(height), type);

		//if (pf->get_error() == PF_OK) {
		
		//}

		delete pf;
	} else {
		RETURN_FALSE;
	}
}

PHP_MINIT_FUNCTION(Map) {	
	zend_class_entry map_class_entry;
	INIT_CLASS_ENTRY(map_class_entry, "Map", map_class_functions);
	//le_mapp = zend_register_list_destructors_ex(destroy_map_resource, NULL, "Map", module_number);
	map_class_entry_ptr = zend_register_internal_class(&map_class_entry TSRMLS_CC);	

	#define CONSTANT(s,c) REGISTER_LONG_CONSTANT((s), (c), CONST_CS | CONST_PERSISTENT)

	CONSTANT("FIND_WALK",  0);
	CONSTANT("FIND_FLY",   1);

	return SUCCESS;
}

PHP_RINIT_FUNCTION(Map) {
	return SUCCESS;
}

PHP_MINFO_FUNCTION(Map) {
	php_info_print_table_start();
	php_info_print_table_row(2, "Map library", "enabled");
	php_info_print_table_row(2, "Version", "1.0");
	php_info_print_table_end();
}

/////////////////////////////////////////////
/////////////////////////////////////////////

// DLL_EXPORT


//#define DLL_EXPORT __declspec(dllexport)
//#define DLL_EXPORT

/*
DLL_EXPORT char *path_get(char *map_data, int map_w, int map_h, int x_src, int y_src, int x_dst, int y_dst, int time) {
	pos *pos_list; int pos_list_count = 0, n, p, d;
	pos myp;
	char *retval = "";

	PATHFIND *pf = new PATHFIND(x_dst, y_dst, x_src, y_src, map_data, map_w, map_h, type);

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
*/