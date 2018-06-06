#!/bin/bash

declare -a apps=("./settings/js/main.js" "./apps/updatenotification/js/merged.js")
root=$(pwd)

for i in "${apps[@]}"
do
	entryFile=$i
	backupFile="$entryFile.orig"
	path=$(dirname "$entryFile")

	# Backup original file
	echo "Backing up $entryFile to $backupFile"
	cp $entryFile $backupFile

	# Make the app
	cd "$path/../"
	make

	# Reset
	cd $root

	# Compare build files
	echo "Comparing $entryFile to the original"
	if ! diff -q $entryFile $backupFile &>/dev/null
	then
		echo "$entryFile build is NOT up-to-date! Please send the proper production build within the pull request"
		cat /root/.npm/_logs/*.log
		exit 2
	else
		echo "$entryFile build is up-to-date"
	fi
done
