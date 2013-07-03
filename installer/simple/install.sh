#!/bin/sh
echo $CWD
echo ""
echo "------------------------------------------------------------------"
echo "|         This is the shell based installer for PEACH            |";
echo "------------------------------------------------------------------"
echo ""

me=`whoami`
if [ "$me" = 'root' ]; then
echo "Please do not run the initial steps as root!";
exit;
fi;

echo "Please enter the installation dir for your new site (e.g /var/www/docs)";
echo "Note: Installation directory MUST be an absolute path:";
read INSTALL_DIR; 

if [ "${INSTALL_DIR:0:1}" != '/' ]; then
echo ""
echo "Stopped.";
exit
fi;

if [ -x "$INSTALL_DIR" ]; then
echo ""
echo "The directory '$INSTALL_DIR' exists,"
echo "are you sure you want to continue (yes continues) ?";
read answer
if [ "$answer" != 'yes' ]; then
exit
fi;
else
mkdir "$INSTALL_DIR";
echo ""
echo "------------------------------------------------------------------"
echo "|         Dir: '$INSTALL_DIR' created                             |";
echo "------------------------------------------------------------------"
fi;

echo ""
echo "The site will be build into '$INSTALL_DIR'";
echo "Press enter to continue...";
read foo; 
echo ""

cd src

for i in `find . -path '*CVS*' -prune -o -print`; do

file=`echo $i | sed 's/^\.[\/]//'`

    if [ "$file" != '.' ]; then
        if test -d "$file"; then
            mkdir "$INSTALL_DIR/$file";
        else
            cp -v --parents "./$file" $INSTALL_DIR;
        fi;
    fi;

done;

cd ..

for file in "peach/index.php"; do

    srcFile="src/$file"
    destFile="$INSTALL_DIR/$file";
    sed  "s,@INSTALL_DIR@,$INSTALL_DIR,g" $srcFile > $destFile

done;

echo ""
echo "Please specify the path to your PEAR installation (e.g. /usr/lib/php ):"
read PEAR_DIR
echo ""
if test -f "$PEAR_DIR/PEAR.php"; then
echo "Good found pear..";
    sed  "s,@PEAR_DIR@,$PEAR_DIR,g" "$INSTALL_DIR/peach/index.php" > .tmp
    mv .tmp "$INSTALL_DIR/peach/index.php";
echo ".--------------------------------------------------------------------."
echo "| added path to PEAR to the include path in index.php                |";
echo " --------------------------------------------------------------------"

else
echo "PEAR Directory not found ! please install it later";
echo "and edit the include path in $INSTALL_DIR/peach/index.php";
fi;

echo ""
echo "Please specify the path to your PEACH installation (or press enter to use the current location):"
read PEACH_DIR 

if test -d "$PEACH_DIR"; then
echo "PEACH DIR set to $PEACH_DIR";
else 
PEACH_DIR="${PWD%/*/*}"
echo "PEACH DIR set to $PEACH_DIR";
fi;

if test -d "$PEACH_DIR"; then
sed  "s,@PEACH_DIR@,$PEACH_DIR,g" "$INSTALL_DIR/peach/index.php" > .tmp
mv .tmp "$INSTALL_DIR/peach/index.php";
else
echo "!!!!! Failed to detect your PEACH installation dir";
fi;

echo ""
echo "Ok, now we need to create a PEAR DB dsn to connect to create some tables in:"
echo "e.g. mysql://user:password@localhost/PEACH"
echo "Database type (e.g. mysql):"
read DB_TYPE
echo "Database user:"
read DB_USER
echo "Database password:"
read DB_PASS
echo "Database hostname:"
read DB_HOST
echo "Database name:"
read DB_NAME

DB_DSN="$DB_TYPE://$DB_USER:$DB_PASS@$DB_HOST/$DB_NAME"
echo "Using $DB_DSN for the database connection";
for file in `ls $INSTALL_DIR/config/*.xml`; do
sed  "s,@DB_DSN@,$DB_DSN,g" $file > .tmp
sed  "s,@INSTALL_DIR@,$INSTALL_DIR,g" .tmp > .tmp2 
mv .tmp2 $file;
rm .tmp
done;
# add the db info and logfile dir to the Propel config file
sed "s,@DB_TYPE@,$DB_TYPE,g" "$INSTALL_DIR/config/PEACH-conf.php" | \
sed "s,@DB_USER@,$DB_USER,g" | \
sed "s,@DB_PASS@,$DB_PASS,g" | \
sed "s,@DB_HOST@,$DB_HOST,g" | \
sed "s,@DB_NAME@,$DB_NAME,g" | \
sed "s,@INSTALL_DIR@,$INSTALL_DIR,g" > .tmp
mv .tmp "$INSTALL_DIR/config/PEACH-conf.php"

echo ""
echo ".--------------------------------------------------------------------."
echo "| Creating Tables !                                                  |";
echo " --------------------------------------------------------------------"

for file in `find "${PWD%/*/*}/PEACH/App" -name install.sql`; do
if [ "$DB_TYPE" = "mysql" ]; then
mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME < $file;
else
echo "I only know how to install the tables for mysql.. $file not installed..";
fi;
done;

echo ""
echo ".--------------------------------------------------------------------."
echo "| Changing file permissions (please review these to suit your needs) |";
echo " --------------------------------------------------------------------"
chmod -v a+rwx "$INSTALL_DIR/config" -R
chmod -v a+rwx "$INSTALL_DIR/sessions" -R
chmod -v a+rwx "$INSTALL_DIR/logs" -R
chmod -v a+rwx "$INSTALL_DIR/compiled" -R
chmod -v a+rwx "$INSTALL_DIR/Data" -R

echo "------------------------------------------------------------------"
echo "|                  Installation done!                            |"
echo "|                                                                |"
echo "|     Be sure to install the PEAR Core libraries and the         |"
echo "|     following packages:                                        |"
echo "|         - HTML_Template_Flexy                                  |"
echo "|         - HTML_QuickForm                                       |"
echo "|                                                                |"
echo "|     Also make sure you have copied the PEACH dir into your     |"
echo "|     php include path, a good location is /usr/lib/php          |"
echo "|     (a sub directory of your pear installation                 |"
echo "|                                                                |"
echo "------------------------------------------------------------------"
