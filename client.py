import time
import os, sys
from socket import *
from fcntl import ioctl
from select import select
import getopt, struct
import subprocess
import urllib

if len(sys.argv) != 5:
    print "Usage: %s [server url] [client_id] [client_ip] [network_mask]" % sys.argv[0]
    sys.exit(1)

VPN_ADDR = sys.argv[3]
VPN_MASK = int(sys.argv[4])
VPN_CLIENT_ID = sys.argv[2]

VPN_URL = sys.argv[1]

TUNSETIFF = 0x400454ca
IFF_TUN   = 0x0001
IFF_TAP   = 0x0002

f = os.open("/dev/net/tun", os.O_RDWR | os.O_NONBLOCK)
ifs = ioctl(f, TUNSETIFF, struct.pack("16sH", "toto%d", IFF_TAP))
ifname = ifs[:16].strip("\x00")

subprocess.call(['ifconfig', ifname, '%s/%d' % (VPN_ADDR, VPN_MASK), 'up'])

try:
    #TODO: Dogadanie z serwerem
    
    # Przesylanie danych
    while 1:
        try:
            data = os.read(f,1500)
            url = "%s/?%s" % (VPN_URL, urllib.urlencode({'data': urllib.quote(data), 'client_id': VPN_CLIENT_ID}))
            resp = urllib.urlopen(url).readlines()
            for line in resp:
                os.write(1, '^')
                os.write(f, urllib.unquote(line))
        except:
            url = "%s/?%s" % (VPN_URL, urllib.urlencode({'client_id': VPN_CLIENT_ID}))
            resp = urllib.urlopen(url).readlines()
            for line in resp:
                os.write(1, 'v')
                os.write(f, urllib.unquote(line))
            time.sleep(0.2)
except KeyboardInterrupt:
    #TODO: Wylogowanie z serwera
    print "Stopped by user."
