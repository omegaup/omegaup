#!/bin/bash -e

if [ $# -lt 2 ]; then
	echo "$0 <grader hostname> <hostname to deploy to> [--upgrade]"
	exit 1
fi

GRADER=$1
HOSTNAME=$2
if [ "$USERNAME" == "" ]; then
	USERNAME=`whoami`
fi

UPGRADE_COMMAND=
if [ "$3" == "--upgrade" ]; then
	UPGRADE_COMMAND="apt-get update -y && apt-get upgrade -y"
fi

ROOT=`dirname $0`/..
TMPDIR=`mktemp -d`
TARGET=$TMPDIR/runner-distrib.sh
JKS=$ROOT/ssl/${HOSTNAME}.jks

make -C $ROOT/minijail

if [ ! -f $JKS ]; then
	$ROOT/bin/certmanager runner --hostname $HOSTNAME --output $JKS
fi

mkdir -p $TMPDIR/distrib/{compile,input,bin}
mkdir -p $TMPDIR/distrib/minijail/{bin,lib,dist,scripts,bin}

cp $ROOT/bin/runner.jar $TMPDIR/distrib/bin/runner.jar
cp $JKS $TMPDIR/distrib/bin/omegaup.jks
cp $ROOT/minijail/{minijail0,ldwrapper,libminijailpreload.so} $TMPDIR/distrib/minijail/bin/
cp $ROOT/bin/{karel,kcl} $TMPDIR/distrib/minijail/bin/
cp $ROOT/stuff/libkarel.py $TMPDIR/distrib/minijail/lib/
cp $ROOT/stuff/mkroot $TMPDIR/distrib/minijail/bin/
cp $ROOT/stuff/runner.service $TMPDIR/distrib/bin
chmod +x $TMPDIR/distrib/bin/runner.service
cp $ROOT/stuff/minijail-scripts/* $TMPDIR/distrib/minijail/scripts/

cat > $TMPDIR/distrib/bin/omegaup.conf <<EOF
{
	"logging": {
		"file": "/opt/omegaup/runner_service.log"
	},
	"common": {
		"roots": {
			"compile": "/opt/omegaup/compile",
			"input": "/opt/omegaup/input"
		},
		"paths": {
			"minijail": "/opt/omegaup/minijail"
		}
	},
	"runner": {
		"port": 21681,
		"register_url": "https://$GRADER:21680/endpoint/register/",
		"deregister_url": "https://$GRADER:21680/endpoint/deregister/",
		"hostname": "$HOSTNAME"
	}
}
EOF

pushd $TMPDIR/distrib
	tar -cjf ../runner-distrib.tar.bz2 *
popd

cat > $TMPDIR/setup-runner <<EOF
#!/bin/bash -e

sudo service runner stop || echo 'No runner found'

$UPGRADE_COMMAND

# Install all required packages
if [ ! -d /opt/omegaup ]; then
	apt-get update -y
	apt-get install -y g++ fp-compiler openjdk-8-jdk ruby libgfortran3 ghc
	mkdir /opt/omegaup
fi

# Install some Haskell packages
#if [ ! -f /usr/bin/cabal ]; then
#  sudo apt-get install cabal-install
#  sudo cabal update
#  sudo cabal install --global vector array mtl logict lens pipes mwc-random hashtables aeson hashmap
#fi

# Install dnsmasq to hardcode the DNS reverse resolution
if [ ! -f /etc/dnsmaq.d/omegaup.conf ]; then
	apt-get install -y dnsmasq
	echo -e 'address=/omegaup.com/162.220.216.152\nptr-record=152.216.220.162.in-addr.arpa,omegaup.com' | sudo tee /etc/dnsmasq.d/omegaup.conf > /dev/null
	sudo service dnsmasq restart
fi

# Minijail needs sudopowers
if [ "\`grep minijail0 /etc/sudoers\`" = "" ]; then
	echo "omegaup ALL = NOPASSWD: /opt/omegaup/minijail/bin/minijail0" >> /etc/sudoers
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
if [ ! -f /etc/init.d/runner ]; then
	sudo cp /opt/omegaup/bin/runner.service /etc/init.d/runner
	sudo update-rc.d runner defaults
fi
sudo service runner restart || true

# Marks the end of the install script and the beginning of the payload
exit
EOF

cat $TMPDIR/setup-runner $TMPDIR/runner-distrib.tar.bz2 > $TARGET

# Deploy the payload
scp $IDENTITY $TARGET $USERNAME@$HOSTNAME:~
ssh $IDENTITY $USERNAME@$HOSTNAME -C "sudo /bin/bash ~/runner-distrib.sh"

rm -rf $TMPDIR
