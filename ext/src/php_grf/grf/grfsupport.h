/*
 *  libgrf
 *  grfsupport.h - commonly used functions
 *  Copyright (C) 2004  Faithful <faithful@users.sf.net>
 *  Copyright (C) 2004  Hongli Lai <h.lai@chello.nl>
 *  Copyright (C) 2004  Rasqual <rasqualtwilight@users.sf.net>
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

#ifndef __GRFSUPPORT_H__
#define __GRFSUPPORT_H__

/* Other includes */
#include "grftypes.h"

/* Comment this before final release */
/* #define GRF_DEBUG 1 */

GRFEXTERN_BEGIN

#ifdef WIN32
	/* Windows function names are so... ugghhhh */
	#if !defined(__MINGW32__) && !defined(_INC_IO)
		#include <io.h>
		#define dup(handle) _dup(handle)
		#define fileno(stream) _fileno(stream)
	#endif /* !defined(__MINGW32__) && !defined(_INC_IO) */
#endif /* defined(WIN32) */

/* GRFINLINE uint8_t LittleEndian8 (uint8_t *p); */	/* Pointless */
/* GRFINLINE uint16_t LittleEndian16 (uint8_t *p); */	/* Unused */
GRFINLINE uint32_t LittleEndian32 (uint8_t *p);
GRFINLINE uint32_t ToLittleEndian32(uint32_t);

GRFEXPORT char *GRF_normalize_path(char *out, const char *in);
GRFEXPORT uint32_t GRF_NameHash(const char *name);

GRFEXPORT void grf_sort (Grf *grf, int(*compar)(const void *, const void *));
#define GRF_AlphaSort ((int(*)(const void *, const void *))GRF_AlphaSort_Func)
GRFEXPORT int GRF_AlphaSort_Func(const GrfFile *g1, const GrfFile *g2);
#define GRF_OffsetSort ((int(*)(const void *, const void *))GRF_OffsetSort_Func)
GRFEXPORT int GRF_OffsetSort_Func(const GrfFile *g1, const GrfFile *g2);

GRFEXPORT GrfFile *grf_find (Grf *grf, const char *fname, uint32_t *index);
GRFEXPORT uint32_t grf_find_unused (Grf *grf, uint32_t len);

GRFEXPORT GrfError *GRF_SetError(GrfError *err, GrfErrorType errtype, uint32_t line, const char *file, const char *func, void *extra);
GRFEXPORT const char *grf_strerror(GrfError err);

/*! \brief Macro used internally
 *
 * \sa GRF_SETERR
 * \sa GRF_SETERR_2
 */
#define GRF_SETERR_ADD(a,b,e,f) GRF_SetError(a,b,__LINE__,__FILE__,#e,(void*)f)
/*! \brief Simplification Macro
 *
 * Simplifies setting an error to a GrfError pointer
 */
#define GRF_SETERR(err,type,func) GRF_SETERR_ADD(err,type,func,0)
/*! \brief Simplificatoin Macro
 *
 * Simplifies setting an error (with extra data) to a GrfError pointer
 */
#define GRF_SETERR_2(err,type,func,extra) GRF_SETERR_ADD(err,type,func,extra)

GRFEXTERN_END

#endif /* !defined(__GRFSUPPORT_H__) */
