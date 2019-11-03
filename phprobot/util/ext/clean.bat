@echo off
rem echo Deleting grfio intermediate files...
@rem del /q grfio\grfio.exp
@rem del /q grfio\grfio.lib
@rem del /q grfio\grfio.ncb
@rem del /q grfio\grfio.obj
@rem del /q grfio\grfio.opt
@rem del /q grfio\grfio.plg
@rem del /q grfio\vc60.idb
echo Deleting path intermediate files...
@del /q path\path.exp
@del /q path\path.lib
@del /q path\path.ncb
@del /q path\path.obj
@del /q path\path.opt
@del /q path\path.plg
@del /q path\vc60.idb
