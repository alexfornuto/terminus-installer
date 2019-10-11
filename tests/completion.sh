#! /bin/bash
touch completionresults.txt
script -c "terminus site:$(xdotool key Tab)" completionresults.txt

printf "\n\n"

if (grep -q "Did you mean one of these?" "completionresults.txt")
then
  echo "Completion works!"
  exit 0
else
  echo "Completion test failed"
  exit 1
fi

