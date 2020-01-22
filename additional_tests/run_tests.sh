#!/bin/sh

# start the built-in PHP server, we need different processes as PHP built-in
# server only accepts one connection at a time...
php -S localhost:8080 -t . >/dev/null 2>/dev/null &
PID1=$!
php -S localhost:8081 -t . >/dev/null 2>/dev/null &
PID2=$!
php -S localhost:8082 -t . >/dev/null 2>/dev/null &
PID3=$!

# wait for PHP to start properly
sleep 1

stop_php() {
    # kill the PHP processes
    kill ${PID1}
    kill ${PID2}
    kill ${PID3}
}

##########
# TEST 1 #
##########

# obtain the SID from the cookie header
SID=$(curl -s -I "http://localhost:8080/session_test.php?a=1" | grep "Set-Cookie" | cut -d ':' -f 2 | cut -d ';' -f 1 | cut -d '=' -f 2)
echo ${SID}

# call "a=2" which will destroy the session but not before waiting 2 seconds 
# which will give the next curl command the possibility to run in the lock and
# block there for another ~ 1 second
curl -s -H "Cookie: SID=${SID}" "http://localhost:8081/session_test.php?a=2" &

# wait 1 second before launching "a=3"...
sleep 1

# ...which will try to get the value from the session...
S3=$(curl -s -H "Cookie: SID=${SID}" "http://localhost:8082/session_test.php?a=3")

# "a=3" should only read the session data *after* "a=2" is finished and thus 
# the session was destroyed
if [ "" != "${S3}" ]
then
    echo "ERROR: expected empty string, got \"${S3}\""
    stop_php
    exit 1
fi

##########
# TEST 2 #
##########

# obtain the SID
SID=$(curl -s -I "http://localhost:8080/session_test.php?a=4" | grep "Set-Cookie" | cut -d ':' -f 2 | cut -d ';' -f 1 | cut -d '=' -f 2)

i=0
while [ $i -le 50 ]
do
    PORT=$(( i % 3))
    (
        curl -s -H "Cookie: SID=${SID}" "http://localhost:808${PORT}/session_test.php?a=5" >/dev/null
    ) &
    i=$(( i + 1 ))
done

# we have to wait sufficiently long for all above curl commands to finish...
sleep 3

S5=$(curl -s -H "Cookie: SID=${SID}" "http://localhost:8080/session_test.php?a=5")
if [ "52" != "${S5}" ]
then
    echo "ERROR: expected \"52\", got \"${S5}\""
    stop_php
    exit 1
fi

# all went fine!
stop_php
echo 'OK'
