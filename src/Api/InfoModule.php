<?php

/*
 * eduVPN - End-user friendly VPN.
 *
 * Copyright: 2016-2018, The Commons Conservancy eduVPN Programme
 * SPDX-License-Identifier: AGPL-3.0+
 */

namespace SURFnet\VPN\Server\Api;

use SURFnet\VPN\Common\Config;
use SURFnet\VPN\Common\Http\ApiResponse;
use SURFnet\VPN\Common\Http\AuthUtils;
use SURFnet\VPN\Common\Http\Request;
use SURFnet\VPN\Common\Http\Service;
use SURFnet\VPN\Common\Http\ServiceModuleInterface;
use SURFnet\VPN\Common\ProfileConfig;

class InfoModule implements ServiceModuleInterface
{
    /** @var \SURFnet\VPN\Common\Config */
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @return void
     */
    public function init(Service $service)
    {
        /* INFO */
        $service->get(
            '/instance_number',
            /**
             * @return \SURFnet\VPN\Common\Http\Response
             */
            function (Request $request, array $hookData) {
                AuthUtils::requireUser($hookData, ['vpn-server-node']);

                return new ApiResponse('instance_number', $this->config->getItem('instanceNumber'));
            }
        );

        $service->get(
            '/profile_list',
            /**
             * @return \SURFnet\VPN\Common\Http\Response
             */
            function (Request $request, array $hookData) {
                AuthUtils::requireUser($hookData, ['vpn-user-portal', 'vpn-server-node']);

                $profileList = [];
                foreach ($this->config->getSection('vpnProfiles')->toArray() as $profileId => $profileData) {
                    $profileConfig = new ProfileConfig($profileData);
                    $profileConfigArray = $profileConfig->toArray();
                    ksort($profileConfigArray);
                    $profileList[$profileId] = $profileConfigArray;
                }

                return new ApiResponse('profile_list', $profileList);
            }
        );
    }
}
