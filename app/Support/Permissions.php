<?php

namespace App\Support;

final class Permissions
{
    public const MANAGE_USERS = 'manage users';
    public const MANAGE_ROLES = 'manage roles';
    public const MANAGE_COMPANY_SETTINGS = 'manage company settings';
    public const MANAGE_TRAVEL_COLUMNS = 'manage travel columns';
    public const MANAGE_SELECT_OPTIONS = 'manage select options';
    public const VIEW_TRAVEL_RECORDS = 'view travel records';
    public const CREATE_TRAVEL_RECORDS = 'create travel records';
    public const EDIT_TRAVEL_RECORDS = 'edit travel records';
    public const DELETE_TRAVEL_RECORDS = 'delete travel records';
    public const VIEW_BILLING = 'view billing';
    public const EXPORT_BILLING = 'export billing';
    public const MANAGE_YEARS = 'manage years';
    public const UNLOCK_YEARS = 'unlock years';

    public const ALL = [
        self::MANAGE_USERS,
        self::MANAGE_ROLES,
        self::MANAGE_COMPANY_SETTINGS,
        self::MANAGE_TRAVEL_COLUMNS,
        self::MANAGE_SELECT_OPTIONS,
        self::VIEW_TRAVEL_RECORDS,
        self::CREATE_TRAVEL_RECORDS,
        self::EDIT_TRAVEL_RECORDS,
        self::DELETE_TRAVEL_RECORDS,
        self::VIEW_BILLING,
        self::EXPORT_BILLING,
        self::MANAGE_YEARS,
        self::UNLOCK_YEARS,
    ];

    public const OPERATORE = [
        self::MANAGE_SELECT_OPTIONS,
        self::VIEW_TRAVEL_RECORDS,
        self::CREATE_TRAVEL_RECORDS,
        self::EDIT_TRAVEL_RECORDS,
    ];

    public const PANEL_ACCESS = [
        self::MANAGE_USERS,
        self::MANAGE_ROLES,
        self::MANAGE_COMPANY_SETTINGS,
        self::MANAGE_TRAVEL_COLUMNS,
        self::MANAGE_SELECT_OPTIONS,
        self::VIEW_TRAVEL_RECORDS,
        self::CREATE_TRAVEL_RECORDS,
        self::EDIT_TRAVEL_RECORDS,
        self::DELETE_TRAVEL_RECORDS,
        self::VIEW_BILLING,
        self::EXPORT_BILLING,
        self::MANAGE_YEARS,
        self::UNLOCK_YEARS,
    ];
}
