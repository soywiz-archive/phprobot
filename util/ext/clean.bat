@echo off
echo Deleting grfio intermediate files...
@del /q grfio\grfio.exp
@del /q grfio\grfio.lib
@del /q grfio\grfio.ncb
@del /q grfio\grfio.obj
@del /q grfio\grfio.opt
@del /q grfio\grfio.plg
@del /q grfio\vc60.idb
echo Deleting path intermediate files...
@del /q path\path.exp
@del /q path\path.lib
@del /q path\path.ncb
@del /q path\path.obj
@del /q path\path.opt
@del /q path\path.plg
@del /q path\vc60.idb
