#!/usr/bin/python3

import argparse
import getpass
import json
import logging
import os.path
import shlex
import subprocess
import sys

class Azure:
    def __init__(self, subscription, resource_group, location):
        self._subscription = subscription
        self._resource_group = resource_group
        self._location = location

    def _nsg_name(self):
        return '%s-%s-nsg' % (self._resource_group, self._location)

    def _vnet_name(self):
        return '%s-%s-vnet' % (self._resource_group, self._location)

    def nic_name(self, vm_name):
        return '%s-nic' % vm_name

    def _run(self, args, check=True):
        logging.debug('Running %s', ' '.join(shlex.quote(arg) for arg in args))
        result = subprocess.run(['azure'] + args,
                                stdin=subprocess.DEVNULL,
                                stdout=subprocess.PIPE,
                                universal_newlines=True, shell=False,
                                check=check)
        logging.debug('Result %d: %s', result.returncode, result.stdout)
        return result

    def network_nsg_show(self):
        result = self._run([
            'network', 'nsg', 'show', '--json',
            '--subscription', self._subscription,
            '--resource-group', self._resource_group,
            '--name', self._nsg_name()])
        if result.returncode != 0:
            return None
        return json.loads(result.stdout)

    def network_nsg_create(self):
        return json.loads(self._run([
            'network', 'nsg', 'create', '--json',
            '--subscription', self._subscription,
            '--resource-group', self._resource_group,
            '--location', self._location,
            '--name', self._nsg_name()]).stdout)

    def network_nsg_rule_create(self, protocol, port, priority):
        return json.loads(self._run([
            'network', 'nsg', 'rule', 'create', '--json',
            '--subscription', self._subscription,
            '--resource-group', self._resource_group,
            '--nsg-name', self._nsg_name(),
            '--name', 'allow-%s-port-%d' % (protocol, port),
            '--protocol', protocol, '--destination-port-range', str(port),
            '--priority', str(priority)]).stdout)

    def network_vnet_show(self):
        result = self._run([
            'network', 'vnet', 'show', '--json',
            '--subscription', self._subscription,
            '--resource-group', self._resource_group,
            '--name', self._vnet_name()])
        if result.returncode != 0:
            return None
        return json.loads(result.stdout)

    def network_vnet_create(self):
        return json.loads(self._run([
            'network', 'vnet', 'create', '--json',
            '--subscription', self._subscription,
            '--resource-group', self._resource_group,
            '--location', self._location,
            '--name', self._vnet_name()]).stdout)

    def network_vnet_subnet_create(self):
        return json.loads(self._run([
            'network', 'vnet', 'subnet', 'create', '--json',
            '--subscription', self._subscription,
            '--resource-group', self._resource_group,
            '--vnet-name', self._vnet_name(),
            '--name', 'default', '--address-prefix', '10.0.0.0/24']).stdout)

    def network_nic_show(self, vm_name):
        return json.loads(self._run([
            'network', 'nic', 'show', '--json',
            '--subscription', self._subscription,
            '--resource-group', self._resource_group,
            '--name', '%s-nic' % vm_name]).stdout)

    def network_nic_create(self, vm_name):
        return json.loads(self._run([
            'network', 'nic', 'create', '--json',
            '--subscription', self._subscription,
            '--resource-group', self._resource_group,
            '--location', self._location, '--name', self.nic_name(vm_name),
            '--subnet-vnet-name', self._vnet_name(),
            '--subnet-name', 'default',
            '--network-security-group-name', self._nsg_name()]).stdout)

    def vm_list(self):
        return json.loads(self._run([
            'vm', 'list', '--json',
            '--subscription', self._subscription]).stdout)

    def vm_show(self, vm_name):
        return json.loads(self._run([
            'vm', 'show', '--json', '--subscription', self._subscription,
            '--resource-group', self._resource_group,
            '--name', vm_name]).stdout)

    def vm_create(self, vm_name, admin_username, ssh_publickey_file,
            os_type='Linux',
            image_urn='Canonical:UbuntuServer:16.04-LTS:latest',
            vm_size='Standard_A1_v2'):
        self._run([
            'vm', 'create', '--json', '--subscription', self._subscription,
            '--resource-group', self._resource_group,
            '--location', self._location, '--name', vm_name,
            '--admin-username', admin_username,
            '--ssh-publickey-file', ssh_publickey_file,
            '--nic-name', self.nic_name(vm_name), '--public-ip-name', vm_name,
            '--public-ip-domain-name', vm_name, '--os-type', os_type,
            '--image-urn', image_urn, '--vm-size', vm_size], check=True)

    def vm_destroy(self, vm_name):
        self._run([
            'vm', 'destroy', '--json', '--subscription', self._subscription,
            '--resource-group', self._resource_group,
            '--location', self._location, '--name', vm_name], check=True)


def _deploy(azure, args):
    deploy_runner_args = [os.path.join(os.path.dirname(sys.argv[0]),
                                       'deploy_runner.py')]
    if args.verbose:
        deploy_runner_args.append('--verbose')

    runner_hostname = '%s.%s.cloudapp.azure.com' % (args.vm_name,
                                                    args.location)

    vm = azure.vm_show(args.vm_name)
    if not vm:
        nsg = azure.network_nsg_show()
        if not nsg:
            nsg = azure.network_nsg_create()
        missing_ports = set(args.ports)
        for rule in nsg['securityRules']:
            missing_ports.remove('%s:%s:%s' % (rule['protocol'].lower(),
                                               rule['destinationPortRange'],
                                               rule['priority']))
        for port in missing_ports:
            protocol, port, priority = port.split(':')
            azure.network_nsg_rule_create(protocol, int(port),
                                          int(priority))

        vnet = azure.network_vnet_show()
        if not vnet:
            vnet = azure.network_vnet_create()
        if not vnet['subnets']:
            azure.network_vnet_subnet_create()

        nic = azure.network_nic_show(args.vm_name)
        if not nic:
            azure.network_nic_create(args.vm_name)

        azure.vm_create(args.vm_name, args.username, args.pubkey_file)

        # Remove any old SSH keys associated with that hostname.
        subprocess.check_call(['/usr/bin/ssh-keygen',
                               '-f', os.path.expanduser('~/.ssh/known_hosts'),
                               '-R', runner_hostname])

        # And accept the new SSH key.
        subprocess.check_call(['/usr/bin/ssh',
                               '-o', 'StrictHostKeyChecking=no',
                               runner_hostname, '/bin/true'])

        deploy_runner_args.append('--upgrade')

    deploy_runner_args.extend(['--certroot', args.certroot, runner_hostname])
    subprocess.check_call(deploy_runner_args)


def _destroy(azure, args):
    vm = azure.vm_show(args.vm_name)
    if vm:
        azure.vm_destroy(args.vm_name)


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument('-v', '--verbose', action='store_true')
    parser.add_argument('--subscription', required=True)
    parser.add_argument('--resource-group', default='omegaup-v2-runner')
    subparsers = parser.add_subparsers(dest='command')

    deploy = subparsers.add_parser('deploy')
    deploy.add_argument('--username', default=getpass.getuser())
    deploy.add_argument('--port', dest='ports', metavar='PORT', nargs='+',
                        default=['tcp:22:1000', 'tcp:6060:1010'])
    deploy.add_argument('--pubkey-file',
                        default=os.path.expanduser('~/.ssh/azure.pub'))
    deploy.add_argument('--certroot', required=True)
    deploy.add_argument('location')
    deploy.add_argument('vm_name', metavar='vm-name')

    destroy = subparsers.add_parser('destroy')
    destroy.add_argument('location')
    destroy.add_argument('vm_name', metavar='vm-name')

    args = parser.parse_args()

    if args.verbose:
        logging.basicConfig(level=logging.DEBUG)
    else:
        logging.basicConfig(level=logging.INFO)

    azure = Azure(args.subscription, args.resource_group, args.location)

    if args.command == 'deploy':
        _deploy(azure, args)
    elif args.command == 'destroy':
        _destroy(azure, args)


if __name__ == '__main__':
    main()
