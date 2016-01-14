<?php

/**
 * Copyright 2015 François Kooman <fkooman@tuxed.net>.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
namespace fkooman\VPN\Server;

use PHPUnit_Framework_TestCase;
use PDO;

class ClientConnectionTest extends PHPUnit_Framework_TestCase
{
    /** @var ClientConnection */
    private $cc;

    public function setUp()
    {
        $io = $this->getMockBuilder('fkooman\IO\IO')->getMock();
        $io->method('getTime')->will($this->returnValue(12345678));

        $this->cc = new ClientConnection(new PDO('sqlite::memory:'), $io);
        $this->cc->initDatabase();
    }

    public function testConnect()
    {
        $this->cc->connect([
            'common_name' => 'foo_vpn_ex_def',
            'time_unix' => '1452535477',
            'ifconfig_pool_remote_ip' => '10.42.42.2',
            'ifconfig_ipv6_remote' => 'fd00:4242:4242::2',
        ]);
    }

    public function testConnectDisconnect()
    {
        $this->cc->connect([
            'common_name' => 'foo_vpn_ex_def',
            'time_unix' => '1452535477',
            'ifconfig_pool_remote_ip' => '10.42.42.2',
            'ifconfig_ipv6_remote' => 'fd00:4242:4242::2',
        ]);
        $this->assertTrue(
            $this->cc->disconnect([
                'common_name' => 'foo_vpn_ex_def',
                'time_unix' => '1452535477',
                'ifconfig_pool_remote_ip' => '10.42.42.2',
                'ifconfig_ipv6_remote' => 'fd00:4242:4242::2',
                'bytes_received' => '4843',
                'bytes_sent' => '5317',
            ])
        );
    }

    public function testDisconnectWithoutMatchingConnect()
    {
        $this->assertFalse(
            $this->cc->disconnect([
                'common_name' => 'foo_vpn_ex_def',
                'time_unix' => '1452535477',
                'ifconfig_pool_remote_ip' => '10.42.42.2',
                'ifconfig_ipv6_remote' => 'fd00:4242:4242::2',
                'bytes_received' => '4843',
                'bytes_sent' => '5317',
            ])
        );
    }

    public function testGetConnectionList()
    {
        $this->cc->connect([
            'common_name' => 'foo_vpn_ex_def',
            'time_unix' => '1452535477',
            'ifconfig_pool_remote_ip' => '10.42.42.2',
            'ifconfig_ipv6_remote' => 'fd00:4242:4242::2',
        ]);
        $this->assertTrue(
            $this->cc->disconnect([
                'common_name' => 'foo_vpn_ex_def',
                'time_unix' => '1452535477',
                'ifconfig_pool_remote_ip' => '10.42.42.2',
                'ifconfig_ipv6_remote' => 'fd00:4242:4242::2',
                'bytes_received' => '4843',
                'bytes_sent' => '5317',
            ])
        );
        $this->assertSame(
            [
                [
                'common_name' => 'foo_vpn_ex_def',
                'time_unix' => '1452535477',
                'ifconfig_pool_remote_ip' => '10.42.42.2',
                'ifconfig_ipv6_remote' => 'fd00:4242:4242::2',
                'bytes_received' => '4843',
                'bytes_sent' => '5317',
                'disconnect_time_unix' => '12345678',
                ],
            ],
            $this->cc->getConnectionHistory()
        );
    }
}
