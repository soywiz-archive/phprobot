#include <windows.h>
#include <process.h>
#include <stdlib.h>
#include <stdio.h>
#include <sys/types.h>
#include <sys/stat.h>

int WINAPI WinMain(HINSTANCE hInstance, HINSTANCE hPrevInstance, LPSTR lpCmdLine, int nCmdShow) {
	char *args[4], *prog = NULL;
	char binary_file[] = "bin\\php-win.exe";
	char modulepath[_MAX_PATH];

	// Look for php.exe in the same directory as php_win.exe
	if (GetModuleFileName(NULL, modulepath, _MAX_PATH)) {
		char *separator_location = strrchr(modulepath, '\\');
		if (separator_location) {
			struct stat statbuf;
			strcpy(separator_location + 1, binary_file);

			//MessageBox(NULL, modulepath, "Error", MB_OK);

			if (stat(modulepath, &statbuf) == 0) {
				if (((statbuf.st_mode & S_IEXEC) == S_IEXEC)) {
					prog = modulepath;
				}
			}
		}
	}

	// Set program parameters
	args[0] = prog;
	//args[1] = lpCmdLine;
	args[1] = "\"src\\Lunea.php\"";
	args[2] = NULL;

	//MessageBox(NULL, prog, "Error", MB_OK);

	if (prog)
		return (int)_spawnvp(_P_DETACH, prog, args);
	else {
		MessageBox(NULL, "PHP.EXE was not found.", "Error", MB_OK);
		return -1;
	}
}