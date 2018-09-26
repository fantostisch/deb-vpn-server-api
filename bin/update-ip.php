#!/usr/bin/env php
<?php

/*
 * eduVPN - End-user friendly VPN.
 *
 * Copyright: 2016-2018, The Commons Conservancy eduVPN Programme
 * SPDX-License-Identifier: AGPL-3.0+
 */

require_once dirname(__DIR__).'/vendor/autoload.php';
$baseDir = dirname(__DIR__);

use SURFnet\VPN\Common\CliParser;
use SURFnet\VPN\Common\Config;
use SURFnet\VPN\Common\ProfileConfig;

/*
 * Update the IP address configuration of vpn-server-api.
 *
 * IPv4:
 * Random value for the second and third octet, e.g: 10.53.129.0/25
 *
 * IPv6:
 * The IPv6 address is generated according to RFC 4193 (Global ID), it results
 * in a /64 network.
 */

try {
    $p = new CliParser(
        'Automatically generate an IP address and basic config for a profile',
        [
            'instance' => ['the VPN instance', true, false],
            'profile' => ['the profile to target, e.g. internet', true, true],
            'host' => ['the hostname clients connect to', true, true],
            'ext' => ['the external interface, e.g. eth0', true, true],
        ]
    );

    $opt = $p->parse($argv);
    if ($opt->hasItem('help')) {
        echo $p->help();
        exit(0);
    }

    $instanceId = $opt->hasItem('instance') ? $opt->getItem('instance') : 'default';
    $v4 = sprintf(
        '10.%s.%s.0/25',
        hexdec(bin2hex(random_bytes(1))),
        hexdec(bin2hex(random_bytes(1)))
    );

    $v6 = sprintf(
        'fd%s:%s:%s:%s::/64',
        bin2hex(random_bytes(1)),
        bin2hex(random_bytes(2)),
        bin2hex(random_bytes(2)),
        bin2hex(random_bytes(2))
    );

    echo sprintf('IPv4 CIDR  : %s', $v4).PHP_EOL;
    echo sprintf('IPv6 prefix: %s', $v6).PHP_EOL;

    $configFile = sprintf('%s/config/%s/config.php', $baseDir, $instanceId);
    $config = Config::fromFile($configFile);
    $profileConfig = new ProfileConfig($config->getSection('vpnProfiles')->getSection($opt->getItem('profile'))->toArray());

    $configData = $config->toArray();
    $profileConfigData = $profileConfig->toArray();

    $profileConfigData['range'] = $v4;
    $profileConfigData['range6'] = $v6;
    $profileConfigData['hostName'] = $opt->getItem('host');
    $profileConfigData['extIf'] = $opt->getItem('ext');
    $configData['vpnProfiles'][$opt->getItem('profile')] = $profileConfigData;

    Config::toFile($configFile, $configData, 0644);
} catch (Exception $e) {
    echo sprintf('ERROR: %s', $e->getMessage()).PHP_EOL;
    exit(1);
}
