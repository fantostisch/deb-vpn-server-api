<?php
/**
 *  Copyright (C) 2016 SURFnet.
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
namespace SURFnet\VPN\Server\Api;

use Psr\Log\LoggerInterface;

/**
 * Handle API calls for Users.
 *
 * XXX more logging!
 */
class UsersModule implements ServiceModuleInterface
{
    /** @var Users */
    private $users;

    /** @var \Psr\Log\LoggerInterface */
    private $logger;

    public function __construct(Users $users, LoggerInterface $logger)
    {
        $this->users = $users;
        $this->logger = $logger;
    }

    public function init(Service $service)
    {
        // DISABLED
        $service->get(
            '/users/disabled',
            function (array $serverData, array $getData, array $postData, array $hookData) {
                Utils::requireUser($hookData, ['admin']);

                return new ApiResponse('users', $this->users->getDisabled());
            }
        );

        $service->get(
            '/users/is_disabled',
            function (array $serverData, array $getData, array $postData, array $hookData) {
                Utils::requireUser($hookData, ['admin']);
                $userId = Utils::requireParameter($getData, 'user_id');
                InputValidation::userId($userId);

                return new ApiResponse('disabled', $this->users->isDisabled($userId));
            }
        );

        $service->post(
            '/users/disable',
            function (array $serverData, array $getData, array $postData, array $hookData) {
                Utils::requireUser($hookData, ['admin']);
                $userId = Utils::requireParameter($postData, 'user_id');
                InputValidation::userId($userId);
                $this->logger->info(sprintf('disabling user "%s"', $userId));

                return new ApiResponse('ok', $this->users->setDisabled($userId));
            }
        );

        $service->post(
            '/users/enable',
            function (array $serverData, array $getData, array $postData, array $hookData) {
                Utils::requireUser($hookData, ['admin']);
                $userId = Utils::requireParameter($postData, 'user_id');
                InputValidation::userId($userId);
                $this->logger->info(sprintf('enabling user "%s"', $userId));

                return new ApiResponse('ok', $this->users->setEnabled($userId));
            }
        );

        // OTP_SECRETS
        $service->get(
            '/users/has_otp_secret',
            function (array $serverData, array $getData, array $postData, array $hookData) {
                Utils::requireUser($hookData, ['admin', 'portal']);
                $userId = Utils::requireParameter($getData, 'user_id');
                InputValidation::userId($userId);

                return new ApiResponse('otp_secret', $this->users->hasOtpSecret($userId));
            }
        );

        $service->post(
            '/users/set_otp_secret',
            function (array $serverData, array $getData, array $postData, array $hookData) {
                Utils::requireUser($hookData, ['portal']);
                $userId = Utils::requireParameter($postData, 'user_id');
                InputValidation::userId($userId);
                $otpSecret = Utils::requireParameter($postData, 'otp_secret');
                InputValidation::otpSecret($otpSecret);

                return new ApiResponse('ok', $this->users->setOtpSecret($userId, $otpSecret));
            }
        );

        $service->post(
            '/users/delete_otp_secret',
            function (array $serverData, array $getData, array $postData, array $hookData) {
                Utils::requireUser($hookData, ['admin']);
                $userId = Utils::requireParameter($postData, 'user_id');
                InputValidation::userId($userId);

                return new ApiResponse('ok', $this->users->deleteOtpSecret($userId));
            }
        );

        // VOOT_TOKENS
        $service->get(
            '/users/has_voot_token',
            function (array $serverData, array $getData, array $postData, array $hookData) {
                Utils::requireUser($hookData, ['portal']);
                $userId = Utils::requireParameter($getData, 'user_id');
                InputValidation::userId($userId);

                return new ApiResponse('voot_token', $this->users->hasVootToken($userId));
            }
        );

        $service->post(
            '/users/set_vook_token',
            function (array $serverData, array $getData, array $postData, array $hookData) {
                Utils::requireUser($hookData, ['admin']);
                $userId = Utils::requireParameter($postData, 'user_id');
                InputValidation::userId($userId);
                $vootToken = Utils::requireParameter($postData, 'voot_token');
                InputValidation::vootToken($vootToken);

                return new ApiResponse('ok', $this->users->setVootToken($userId, $vootToken));
            }
        );
    }
}