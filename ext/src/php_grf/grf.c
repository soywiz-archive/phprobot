#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include "php.h"
#include "php_globals.h"
#include "ext/standard/info.h"

#include "php_grf.h"

#include "gpattern/gpattern.h"

static int le_grfp;

static zend_class_entry *grf_class_entry_ptr;

static zend_function_entry grf_functions[] = {
	{ NULL, NULL, NULL }
};

static void destroy_grf_resource(zend_rsrc_list_entry *resource TSRMLS_DC) {
	//grf_close((Grfptr)resource->ptr);

	//if (!resource->ptr)
		grf_free((Grfptr)resource->ptr);
}


static zend_function_entry grf_class_functions[] = {
	PHP_ME(Grf, __construct,      NULL, 0)
	PHP_ME(Grf, Find,             NULL, 0)
	PHP_ME(Grf, Read,             NULL, 0)
	PHP_ME(Grf, ReadIndex,        NULL, 0)
	{ NULL, NULL, NULL }
};

zend_module_entry grf_module_entry = {
	STANDARD_MODULE_HEADER,
	"Grf",
	grf_functions,
	PHP_MINIT(Grf), /* module init function */
	NULL,           /* module shutdown function */
	PHP_RINIT(Grf), /* request init function */
	NULL,           /* request shutdown function */
	PHP_MINFO(Grf), /* module info function */
	NO_VERSION_YET,
	STANDARD_MODULE_PROPERTIES
};

ZEND_GET_MODULE(grf)

static void *PHPgetProperty(zval *id, char *name, int namelen, int proptype TSRMLS_DC)
{
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


static Grfptr getGrf(zval *id TSRMLS_DC){
	void *grf = PHPgetProperty(id, "grf", 3, le_grfp TSRMLS_CC);

	if (!grf) { php_error_docref(NULL TSRMLS_CC, E_ERROR, "Called object is not an Grf"); }
	return (Grfptr)grf;
}


PHP_METHOD(Grf, __construct) {	
	Grfptr mygrf;
	zval **filename;
	int ret;

	if (ZEND_NUM_ARGS() != 1 || zend_get_parameters_ex(1, &filename) == FAILURE) {
		WRONG_PARAM_COUNT;
	}

	convert_to_string_ex(filename);
	mygrf = grf_callback_open(Z_STRVAL_PP(filename), "rb", NULL, NULL);

	if (!mygrf) {
		object_init_ex(getThis(), grf_class_entry_ptr);
		add_property_long(getThis(), "count", 0);
		php_error_docref(NULL TSRMLS_CC, E_NOTICE, "File not exists", "Grf");
	} else {
		ret = zend_list_insert(mygrf, le_grfp);
		
		object_init_ex(getThis(), grf_class_entry_ptr);
		add_property_resource(getThis(), "grf", ret);
		add_property_long(getThis(), "count", mygrf->nfiles);
		zend_list_addref(ret);
	}
	//RETURN_FALSE;
}

int inline grffile_is_dir(GrfFile *gf) {
	return
		(gf->compressed_len_aligned == 0x0714) &&
		(gf->compressed_len         == 0x0449) &&
		(gf->real_len               == 0x055C) &&
		(gf->pos                    == 0x058A);
}

PHP_METHOD(Grf, Find) {
	zval **pattern;
	unsigned int n, t;
	Grfptr mygrf;

	if (ZEND_NUM_ARGS() != 1 || zend_get_parameters_ex(1, &pattern) == FAILURE) WRONG_PARAM_COUNT;	

	convert_to_string_ex(pattern);

	mygrf = getGrf(getThis() TSRMLS_CC);

	array_init(return_value);
	t = mygrf->nfiles;
	for (n = 0; n < t; n++) {
		if (g_pattern_match_simple(Z_STRVAL_PP(pattern), mygrf->files[n].name)) {
			if (grffile_is_dir(&mygrf->files[n])) {
				continue;
			} else {
				add_index_string(return_value, n, mygrf->files[n].name, 1);
			}
		}
	}
}

PHP_METHOD(Grf, ReadIndex) {
	zval **index;
	Grfptr mygrf;
	uint32_t size;
	void *ptr;
	//int ret;

	if (ZEND_NUM_ARGS() != 1 || zend_get_parameters_ex(1, &index) == FAILURE) {
		WRONG_PARAM_COUNT;
	}

	convert_to_long_ex(index);

	mygrf = getGrf(getThis() TSRMLS_CC);

	ptr = grf_index_get(mygrf, Z_LVAL_PP(index), &size, NULL);

	if (ptr == NULL) {
		RETURN_FALSE;
	} else {
		RETURN_STRINGL(ptr, size, 1);
	}
}

PHP_METHOD(Grf, Read) {
	zval **index;
	Grfptr mygrf;
	uint32_t size;
	void *ptr;
	//int ret;

	if (ZEND_NUM_ARGS() != 1 || zend_get_parameters_ex(1, &index) == FAILURE) {
		WRONG_PARAM_COUNT;
	}

	convert_to_string_ex(index);

	mygrf = getGrf(getThis() TSRMLS_CC);

	ptr = grf_get(mygrf, Z_STRVAL_PP(index), &size, NULL);

	if (ptr == NULL) {
		RETURN_FALSE;
	} else {
		RETURN_STRINGL(ptr, size, 1);
	}
}

PHP_MINIT_FUNCTION(Grf) {
	zend_class_entry grf_class_entry;

	INIT_CLASS_ENTRY(grf_class_entry, "Grf", grf_class_functions);

	le_grfp = zend_register_list_destructors_ex(destroy_grf_resource, NULL, "Grf", module_number);

	grf_class_entry_ptr = zend_register_internal_class(&grf_class_entry TSRMLS_CC);

	return SUCCESS;
}

PHP_RINIT_FUNCTION(Grf) {
	return SUCCESS;
}

PHP_MINFO_FUNCTION(Grf) {
	php_info_print_table_start();
	php_info_print_table_row(2, "Grf library", "enabled");
	php_info_print_table_row(2, "Version", "1.0");
	php_info_print_table_end();
}
