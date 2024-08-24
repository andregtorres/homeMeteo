#!/bin/bash
#place inside plots dir
source ../processing/ftpConnectionDetails.pw

# $1 is the first argument to the script
# We are using it as upload directory path
# If it is '.', file is uploaded to current directory.
DESTINATION="plots/"


# Rest of the arguments are a list of files to be uploaded.
# ${@:2} is an array of arguments without first one.
#ALL_FILES="${@:2}"
mapfile -t ALL_FILES1 < newPlots.txt
ALL_FILES=${ALL_FILES1[@]}
echo $ALL_FILES

# FTP login and upload is explained in paragraph below
ftp -inv $HOST <<EOF
passive
user $USER $PASSWORD
cd $DESTINATION
mput $ALL_FILES
bye
EOF

rm newPlots.txt
