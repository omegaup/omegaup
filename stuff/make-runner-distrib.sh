#!/bin/bash

if [ $# -lt 1 ]; then
	echo "$0 <hostname the runners must connect to>"
	exit 1
fi

ROOT=`dirname $0`/..
TARGET=$ROOT/frontend/www/runner-distrib.sh
HOSTNAME=$1

pushd $ROOT/minijail
	make
popd

pushd $ROOT/backend
	sbt proguard:proguard
popd

TMPDIR=`mktemp -d`
mkdir -p $TMPDIR/distrib/{compile,input}
mkdir -p $TMPDIR/distrib/minijail/{bin,dist,scripts,bin}

cp $ROOT/backend/runner/target/scala-2.10/proguard/runner_2.10-1.1.jar $TMPDIR/distrib/runner.jar
cp $ROOT/minijail/{minijail0,ldwrapper,libminijailpreload.so} $TMPDIR/distrib/minijail/bin/
cp $ROOT/bin/{karel,kcl} $TMPDIR/distrib/minijail/bin/
cp $ROOT/stuff/mkroot $TMPDIR/distrib/minijail/bin/
cp $ROOT/stuff/minijail-scripts/* $TMPDIR/distrib/minijail/scripts/

cat > $TMPDIR/distrib/runner.sh <<EOF
#!/bin/bash

cd "\$( dirname "\$0" )"

killall java 2>/dev/null || true
rm -f nohup.out 2>/dev/null || true
rm -f runner.log 2>/dev/null || true
su -c 'nohup /usr/bin/java -jar runner.jar &' omegaup
chown -R omegaup.omegaup nohup.out || true

exit
EOF
chmod +x $TMPDIR/distrib/runner.sh

cat > $TMPDIR/distrib/omegaup.conf <<EOF
# Logging
logging.level=info
logging.file=/opt/omegaup/runner.log

# Use minijail
runner.minijail.path=minijail
runner.sandbox=minijail

# Paths
compile.root=compile
grader.root=grade
input.root=input
problems.root=problems
submissions.root=submissions
runner.sandbox.path=sandbox

# Ports & Endpoints
grader.register.url = https://$HOSTNAME:21680/register/
runner.port = 21681
EOF

pushd $TMPDIR/distrib
	tar -cjf ../runner-distrib.tar.bz2 *
popd

cat > $TMPDIR/setup-runner <<EOF
#!/bin/bash -e

# Install all required packages
if [ ! -d /opt/omegaup ]; then
	apt-get update -y
	apt-get install -y g++ fp-compiler openjdk-7-jdk ruby libgfortran3 ghc
	mkdir /opt/omegaup
fi

# Minijail needs sudopowers
if [ "\`grep omegaup /etc/sudoers\`" = "" ]; then
	echo "omegaup ALL = NOPASSWD: /opt/omegaup/minijail/bin/minijail0" >> /etc/sudoers
fi

# Download the certificate
if [ ! -f /opt/omegaup/omegaup.jks ]; then
	scp runner@$HOSTNAME:$ROOT/backend/runner/omegaup.jks /opt/omegaup/omegaup.jks
fi

# Add the user if not present
id omegaup > /dev/null 2>&1
if [ \$? -eq 1 ]; then
	useradd omegaup
fi

# Extract the payload
sed -e '1,/^exit$/d' "\$0" | tar -C /opt/omegaup -xjf -
chown -R omegaup /opt/omegaup

# Setup the minijail environment
pushd /opt/omegaup/minijail
	python bin/mkroot
popd

# And finally start the runner.
/opt/omegaup/runner.sh

# Marks the end of the install script and the beginning of the payload
exit
EOF

cat $TMPDIR/setup-runner $TMPDIR/runner-distrib.tar.bz2 > $TARGET

rm -rf $TMPDIR
