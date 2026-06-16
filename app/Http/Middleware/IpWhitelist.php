<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IpWhitelist
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get whitelisted subnets from config
        $whitelist = config('vxi.allowed_subnets', [
            '192.168.0.0/16',      // Internal LAN
            '10.0.0.0/8',          // Private network
            '172.16.0.0/12',       // Private network
            '127.0.0.1',           // Localhost for testing
        ]);

        // Get the client's IP address
        $clientIp = $request->ip();

        // Check if client IP is in the whitelist
        if (!$this->isIpInWhitelist($clientIp, $whitelist)) {
            return response()->json([
                'message' => 'Access denied. Your IP address is not authorized to access this application.',
                'client_ip' => $clientIp,
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }

    /**
     * Check if the given IP is within any of the whitelisted subnets.
     */
    private function isIpInWhitelist(string $ip, array $whitelist): bool
    {
        foreach ($whitelist as $subnet) {
            if ($this->isIpInSubnet($ip, $subnet)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if an IP is within a CIDR subnet.
     */
    private function isIpInSubnet(string $ip, string $subnet): bool
    {
        // Handle exact IP matches
        if (strpos($subnet, '/') === false) {
            return $ip === $subnet;
        }

        // Handle CIDR notation
        [$subnetIp, $bits] = explode('/', $subnet);

        $ip = ip2long($ip);
        $subnetIp = ip2long($subnetIp);
        $mask = -1 << (32 - $bits);
        $subnetIp &= $mask;
        $ip &= $mask;

        return $ip === $subnetIp;
    }
}
