#ifndef	_GRFIO_H_
#define	_GRFIO_H_

#ifdef __cplusplus
extern "C" {
#endif

#define DLL_EXPORT __declspec(dllexport)

DLL_EXPORT void  grfio_init(void);
DLL_EXPORT int   grfio_add(char *str);
DLL_EXPORT void *grfio_read(char *name);
DLL_EXPORT void *grfio_reads(char *name, int *size);
DLL_EXPORT int   grfio_size(char *name);
DLL_EXPORT int   grfio_read_save(char *name, char *saveto);

#ifdef __cplusplus
}
#endif

#endif
