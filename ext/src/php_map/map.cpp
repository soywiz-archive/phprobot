#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include "php.h"
#include "php_globals.h"
#include "ext/standard/info.h"
#include "ext/standard/php_array.h"

#include "php_map.h"
#include "Path/path.h"

static zend_class_entry *map_class_entry_ptr;

static zend_function_entry map_functions[] = {
	{ NULL, NULL, NULL }
};

static zend_function_entry map_class_functions[] = {
	PHP_ME(Map, __construct,      NULL, 0)
	PHP_ME(Map, Update,           NULL, 0)
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

void zend_call_any_function(INTERNAL_FUNCTION_PARAMETERS, char *function_name, zval **returnvalue, int number_of_arguments, zval ***params) {
	zval *zval_function_name;

	MAKE_STD_ZVAL(zval_function_name);
	ZVAL_STRING(zval_function_name, function_name, 1);
	if (call_user_function_ex(EG(function_table), NULL, zval_function_name, returnvalue, number_of_arguments, params, 0, NULL TSRMLS_CC) == FAILURE) {
		php_error_docref(NULL TSRMLS_CC, E_ERROR, "function '%s' not found", Z_STRVAL_P(zval_function_name));
	}
	zval_dtor(zval_function_name); /* to free stringvalue memory */
	FREE_ZVAL(zval_function_name);
}


PHP_METHOD(Map, __construct) {	
	zval **width, **height, **data;

	if (ZEND_NUM_ARGS() != 3 || zend_get_parameters_ex(3, &data, &width, &height) == FAILURE) WRONG_PARAM_COUNT;	

	convert_to_string_ex(data);
	convert_to_long_ex(width);
	convert_to_long_ex(height);

	object_init_ex(getThis(), map_class_entry_ptr);
	add_property_zval(getThis(), "data", *data);
	add_property_long(getThis(), "width", Z_LVAL_PP(width));
	add_property_long(getThis(), "height", Z_LVAL_PP(height));
}

PHP_METHOD(Map, Update) {	
	zval **width, **height, **data;

	if (ZEND_NUM_ARGS() != 3 || zend_get_parameters_ex(3, &data, &width, &height) == FAILURE) WRONG_PARAM_COUNT;	

	convert_to_string_ex(data);
	convert_to_long_ex(width);
	convert_to_long_ex(height);

	add_property_zval(getThis(), "data", *data);
	add_property_long(getThis(), "width", Z_LVAL_PP(width));
	add_property_long(getThis(), "height", Z_LVAL_PP(height));
}

PHP_METHOD(Map, __set) {
	zval *val, *tmp;
	char *var;
	int var_l;

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
	zval *width, *height, *data;
	int x, y;

	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "ll", &x, &y) == FAILURE) WRONG_PARAM_COUNT;	

	width  = zend_read_property(map_class_entry_ptr, getThis(), "width",  5, 1 TSRMLS_CC);
	height = zend_read_property(map_class_entry_ptr, getThis(), "height", 6, 1 TSRMLS_CC);
	data   = zend_read_property(map_class_entry_ptr, getThis(), "data",   4, 1 TSRMLS_CC);

	if (x < 0 || y < 0 || x >= Z_LVAL_P(width) || y >= Z_LVAL_P(height)) {
		RETURN_FALSE;
	} else {
		RETURN_LONG((int)((Z_STRVAL_P(data))[y * Z_LVAL_P(width) + x]));
	}
}

PHP_METHOD(Map, Put) {
	zval *width, *height, *data;
	int x, y, set;

	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "lll", &x, &y, &set) == FAILURE) WRONG_PARAM_COUNT;	

	width  = zend_read_property(map_class_entry_ptr, getThis(), "width",  5, 1 TSRMLS_CC);
	height = zend_read_property(map_class_entry_ptr, getThis(), "height", 6, 1 TSRMLS_CC);
	data   = zend_read_property(map_class_entry_ptr, getThis(), "data",   4, 1 TSRMLS_CC);

	if (x < 0 || y < 0 || x >= Z_LVAL_P(width) || y >= Z_LVAL_P(height)) {
		RETURN_FALSE;
	} else {
		(Z_STRVAL_P(data))[y * Z_LVAL_P(width) + x] = (char)set;
		RETURN_TRUE;
	}
}

PHP_METHOD(Map, Find) {
	zval *arrayXY;
	zval *width, *height, *data;
	zval *retval;
	zval **arguments[1];
	int x_src, y_src, x_dst, y_dst, type;
	short mx, my;

	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "lllll", &x_src, &y_src, &x_dst, &y_dst, &type) == FAILURE) WRONG_PARAM_COUNT;	

	width  = zend_read_property(map_class_entry_ptr, getThis(), "width",  5, 1 TSRMLS_CC);
	height = zend_read_property(map_class_entry_ptr, getThis(), "height", 6, 1 TSRMLS_CC);
	data   = zend_read_property(map_class_entry_ptr, getThis(), "data",   4, 1 TSRMLS_CC);

	if (Z_STRLEN_P(data) >= Z_LVAL_P(width) * Z_LVAL_P(height)) {
		PATHFIND *pf = new PATHFIND((short)x_dst, (short)y_dst, (short)x_src, (short)y_src, Z_STRVAL_P(data), Z_LVAL_P(width), Z_LVAL_P(height), type);

		array_init(return_value);

		if (pf->get_error() == PF_OK) {		
			do {
				pf->get_actual_node(mx, my);

				MAKE_STD_ZVAL(arrayXY);
				array_init(arrayXY);
				add_next_index_long(arrayXY, (int)mx);
				add_next_index_long(arrayXY, (int)my);

				//printf("%i, %i\n", (int)mx, (int)my);

				add_next_index_zval(return_value, arrayXY);
			} while (pf->next_node() == PF_OK);

			arguments[0] = &return_value;
			zend_call_any_function(INTERNAL_FUNCTION_PARAM_PASSTHRU, "array_reverse", &retval, 1, arguments);
			*return_value = *retval;
		} else {
			MAKE_STD_ZVAL(arrayXY);
			array_init(arrayXY);
			add_next_index_long(arrayXY, x_src);
			add_next_index_long(arrayXY, y_src);

			add_next_index_zval(return_value, arrayXY);
		}

		delete pf;
	} else {
		RETURN_FALSE;
	}
}

PHP_MINIT_FUNCTION(Map) {	
	zend_class_entry map_class_entry;
	INIT_CLASS_ENTRY(map_class_entry, "Map", map_class_functions);
	map_class_entry_ptr = zend_register_internal_class(&map_class_entry TSRMLS_CC);	

	#define CONSTANT(s,c) REGISTER_LONG_CONSTANT((s), (c), CONST_CS | CONST_PERSISTENT)

	CONSTANT("FIND_WALK",  FIND_WALK);
	CONSTANT("FIND_FLY",   FIND_FLY);

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