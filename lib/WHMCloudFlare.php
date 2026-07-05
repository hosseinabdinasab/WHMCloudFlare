<?php

final class WHMCloudFlare {
    public static function onAccountCreate(array $hook): void {
        if (!Config::get('auto_create_dns', true)) {
            return;
        }
        $domain = WHMAccount::domainFromHook($hook);
        $ip = WHMAccount::ipFromHook($hook);
        if (!$domain || !$ip) {
            Logger::info('Create hook missing domain or IP', ['hook' => $hook]);
            return;
        }
        ZoneManager::upsertARecord($domain, $ip);
    }

    public static function onAccountRemove(array $hook): void {
        if (!Config::get('auto_delete_dns', true)) {
            return;
        }
        $domain = WHMAccount::domainFromHook($hook);
        if (!$domain) {
            return;
        }
        ZoneManager::deleteARecord($domain);
    }

    public static function onSiteIpSet(array $hook): void {
        if (!Config::get('auto_update_ip', true)) {
            return;
        }
        $domain = WHMAccount::domainFromHook($hook);
        $ip = WHMAccount::ipFromHook($hook);
        if (!$domain || !$ip) {
            Logger::info('SiteIP hook missing domain or IP', ['hook' => $hook]);
            return;
        }
        ZoneManager::upsertARecord($domain, $ip);
    }
}
