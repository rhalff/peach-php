#!/bin/sh
# Very simple package creator
TYPE=snap
VERSION=`date +"%Y-%m-%d"`
tar cf PEACH-$TYPE-$VERSION.tgz ChangeLog LICENSE README PEACH installer/simple -z --exclude="CVS" 
