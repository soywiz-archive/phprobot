/* GLIB - Library of useful routines for C programming
 * Copyright (C) 1995-1997, 1999  Peter Mattis, Red Hat, Inc.
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the
 * Free Software Foundation, Inc., 59 Temple Place - Suite 330,
 * Boston, MA 02111-1307, USA.
 */
#include "gpattern.h"

//#include "gmacros.h"
//#include "gmessages.h"
//#include "gmem.h"
//#include "gunicode.h"
//#include "gutils.h" 
#include <string.h>

/* keep enum and structure of gpattern.c and patterntest.c in sync */
typedef enum
{
  G_MATCH_ALL,       /* "*A?A*" */
  G_MATCH_ALL_TAIL,  /* "*A?AA" */
  G_MATCH_HEAD,      /* "AAAA*" */
  G_MATCH_TAIL,      /* "*AAAA" */
  G_MATCH_EXACT,     /* "AAAAA" */
  G_MATCH_LAST
} GMatchType;

struct _GPatternSpec
{
  GMatchType match_type;
  guint      pattern_length;
  guint      min_length;
  gchar     *pattern;
};

/* --- functions --- */
/**
 * g_utf8_reverse:
 * @string: a UTF-8 string.
 *
 * Reverses a UTF-8 string. The @string must be valid UTF-8 encoded text. 
 * (Use g_utf8_validate() on all text before trying to use UTF-8 
 * utility functions with it.)
 *
 * Note that unlike g_strreverse(), this function returns
 * newly-allocated memory, which should be freed with g_free() when
 * no longer needed. 
 *
 * Returns: a newly-allocated string which is the reverse of @string.
 */
static gchar *
g_utf8_reverse (guint len, const gchar *string)
{
  gchar *result;
  const gchar *p;
  gchar *m, *r, skip;

  result = g_new (gchar, len + 1);
  r = result + len;
  p = string;
  while (*p) 
    {
      //skip = g_utf8_skip[*(guchar*)p];
	  skip = 1;
      r -= skip;
      for (m = r; skip; skip--)
        *m++ = *p++;
    }
  result[len] = 0;

  //printf("%s, %s", result, string);

  return result;
}

static inline gboolean
g_pattern_ph_match (const gchar *match_pattern,
		    const gchar *match_string)
{
  register const gchar *pattern, *string;
  register gchar ch;

  pattern = match_pattern;
  string = match_string;

  ch = *pattern;
  pattern++;
  while (ch)
    {
      switch (ch)
	{
	case '?':
	  if (!*string)
	    return FALSE;
	  string = g_utf8_next_char (string);
	  break;

	case '*':
	  do
	    {
	      ch = *pattern;
	      pattern++;
	      if (ch == '?')
		{
		  if (!*string)
		    return FALSE;
		  string = g_utf8_next_char (string);
		}
	    }
	  while (ch == '*' || ch == '?');
	  if (!ch)
	    return TRUE;
	  do
	    {
	      while (ch != *string)
		{
		  if (!*string)
		    return FALSE;
		  string = g_utf8_next_char (string);
		}
	      string++;
	      if (g_pattern_ph_match (pattern, string))
		return TRUE;
	    }
	  while (*string);
	  break;

	default:
	  if (ch == *string)
	    string++;
	  else
	    return FALSE;
	  break;
	}

      ch = *pattern;
      pattern++;
    }

  return *string == 0;
}

gboolean
g_pattern_match (GPatternSpec *pspec,
		 guint         string_length,
		 const gchar  *string,
		 const gchar  *string_reversed)
{
  g_return_val_if_fail (pspec != NULL, FALSE);
  g_return_val_if_fail (string != NULL, FALSE);

  if (pspec->min_length > string_length)
    return FALSE;

  switch (pspec->match_type)
    {
    case G_MATCH_ALL:
      return g_pattern_ph_match (pspec->pattern, string);
    case G_MATCH_ALL_TAIL:
      if (string_reversed)
	return g_pattern_ph_match (pspec->pattern, string_reversed);
      else
	{
          gboolean result;
          gchar *tmp;
	  tmp = g_utf8_reverse (string_length, string);
	  result = g_pattern_ph_match (pspec->pattern, tmp);
	  g_free (tmp);
	  return result;
	}
    case G_MATCH_HEAD:
      if (pspec->pattern_length == string_length)
	return strcmp (pspec->pattern, string) == 0;
      else if (pspec->pattern_length)
	return strncmp (pspec->pattern, string, pspec->pattern_length) == 0;
      else
	return TRUE;
    case G_MATCH_TAIL:
      if (pspec->pattern_length)
        return strcmp (pspec->pattern, string + (string_length - pspec->pattern_length)) == 0;
      else
	return TRUE;
    case G_MATCH_EXACT:
      if (pspec->pattern_length != string_length)
	return FALSE;
      else
	return strcmp (pspec->pattern, string) == 0;
    default:
      g_return_val_if_fail (pspec->match_type < G_MATCH_LAST, FALSE);
      return FALSE;
    }
}

GPatternSpec*
g_pattern_spec_new (const gchar *pattern)
{
  GPatternSpec *pspec;
  gboolean seen_joker = FALSE, seen_wildcard = FALSE, more_wildcards = FALSE;
  gint hw_pos = -1, tw_pos = -1, hj_pos = -1, tj_pos = -1;
  gboolean follows_wildcard = FALSE;
  guint pending_jokers = 0;
  const gchar *s;
  gchar *d;
  guint i;
  
  g_return_val_if_fail (pattern != NULL, NULL);

  /* canonicalize pattern and collect necessary stats */
  pspec = g_new (GPatternSpec, 1);
  pspec->pattern_length = strlen (pattern);
  pspec->min_length = 0;
  pspec->pattern = g_new (gchar, pspec->pattern_length + 1);
  d = pspec->pattern;
  for (i = 0, s = pattern; *s != 0; s++)
    {
      switch (*s)
	{
	case '*':
	  if (follows_wildcard)	/* compress multiple wildcards */
	    {
	      pspec->pattern_length--;
	      continue;
	    }
	  follows_wildcard = TRUE;
	  if (hw_pos < 0)
	    hw_pos = i;
	  tw_pos = i;
	  break;
	case '?':
	  pending_jokers++;
	  pspec->min_length++;
	  continue;
	default:
	  for (; pending_jokers; pending_jokers--, i++) {
	    *d++ = '?';
  	    if (hj_pos < 0)
	     hj_pos = i;
	    tj_pos = i;
	  }
	  follows_wildcard = FALSE;
	  pspec->min_length++;
	  break;
	}
      *d++ = *s;
      i++;
    }
  for (; pending_jokers; pending_jokers--) {
    *d++ = '?';
    if (hj_pos < 0)
      hj_pos = i;
    tj_pos = i;
  }
  *d++ = 0;
  seen_joker = hj_pos >= 0;
  seen_wildcard = hw_pos >= 0;
  more_wildcards = seen_wildcard && hw_pos != tw_pos;

  /* special case sole head/tail wildcard or exact matches */
  if (!seen_joker && !more_wildcards)
    {
      if (pspec->pattern[0] == '*')
	{
	  pspec->match_type = G_MATCH_TAIL;
          memmove (pspec->pattern, pspec->pattern + 1, --pspec->pattern_length);
	  pspec->pattern[pspec->pattern_length] = 0;
	  return pspec;
	}
      if (pspec->pattern[pspec->pattern_length - 1] == '*')
	{
	  pspec->match_type = G_MATCH_HEAD;
	  pspec->pattern[--pspec->pattern_length] = 0;
	  return pspec;
	}
      if (!seen_wildcard)
	{
	  pspec->match_type = G_MATCH_EXACT;
	  return pspec;
	}
    }

  /* now just need to distinguish between head or tail match start */
  tw_pos = pspec->pattern_length - 1 - tw_pos;	/* last pos to tail distance */
  tj_pos = pspec->pattern_length - 1 - tj_pos;	/* last pos to tail distance */
  if (seen_wildcard)
    pspec->match_type = tw_pos > hw_pos ? G_MATCH_ALL_TAIL : G_MATCH_ALL;
  else /* seen_joker */
    pspec->match_type = tj_pos > hj_pos ? G_MATCH_ALL_TAIL : G_MATCH_ALL;
  if (pspec->match_type == G_MATCH_ALL_TAIL) {
    gchar *tmp = pspec->pattern;
    pspec->pattern = g_utf8_reverse (pspec->pattern_length, pspec->pattern);
    g_free (tmp);
  }
  return pspec;
}

void
g_pattern_spec_free (GPatternSpec *pspec)
{
  g_return_if_fail (pspec != NULL);

  g_free (pspec->pattern);
  g_free (pspec);
}

gboolean
g_pattern_spec_equal (GPatternSpec *pspec1,
		      GPatternSpec *pspec2)
{
  g_return_val_if_fail (pspec1 != NULL, FALSE);
  g_return_val_if_fail (pspec2 != NULL, FALSE);

  return (pspec1->pattern_length == pspec2->pattern_length &&
	  pspec1->match_type == pspec2->match_type &&
	  strcmp (pspec1->pattern, pspec2->pattern) == 0);
}

gboolean
g_pattern_match_string (GPatternSpec *pspec,
			const gchar  *string)
{
  g_return_val_if_fail (pspec != NULL, FALSE);
  g_return_val_if_fail (string != NULL, FALSE);

  return g_pattern_match (pspec, strlen (string), string, NULL);
}

gboolean g_pattern_match_simple (const gchar *pattern, const gchar *string) {
	GPatternSpec *pspec;
	gboolean ergo;

	g_return_val_if_fail (pattern != NULL, FALSE);
	g_return_val_if_fail (string != NULL, FALSE);

	pspec = g_pattern_spec_new (pattern);
	ergo = g_pattern_match (pspec, strlen (string), string, NULL);
	g_pattern_spec_free (pspec);

	return ergo;
}