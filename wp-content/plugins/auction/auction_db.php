<?php
class Auction_Database {

    const DATES_TABLE_PREFIX     = 'auction_dates';
    const REGIONS_TABLE_PREFIX   = 'auction_regions';
    const COUNTRIES_TABLE_PREFIX = 'auction_countries';
    const ADDRESS_TABLE_PREFIX   = 'auction_address';
    const ZIPCODES_TABLE_PREFIX  = 'auction_zipcodes';
    

    public function __construct() {
        add_action('init', array(&$this, 'create_dates_table'));
        add_action('init', array(&$this, 'create_countries_table'));
        add_action('init', array(&$this, 'create_regions_table'));
        add_action('init', array(&$this, 'create_zip_codes_table'));
        add_action('init', array(&$this, 'create_address_table'));
    }

    public function create_dates_table() {
        global $wpdb;
        $dates_table = $wpdb->prefix . self::DATES_TABLE_PREFIX;

        if($wpdb->get_var("show tables like '$dates_table'") !== $dates_table) {
            $sql = "CREATE TABLE " . $dates_table . " (
                    `post_id` BIGINT(20) UNSIGNED NOT NULL,
                    `start` DATE NOT NULL,
                    `end` DATE NOT NULL,
                    PRIMARY KEY (`post_id`, `start`, `end`),
                    INDEX `fk_dates_post_idx` (`post_id` ASC),
                    CONSTRAINT `fk_dates_product`
                        FOREIGN KEY (`post_id`)
                        REFERENCES `$wpdb->posts` (`ID`)
                        ON DELETE CASCADE
                        ON UPDATE NO ACTION
                    ) ENGINE = InnoDB;
                ";
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            if (!dbDelta($sql)) {
                return;
            }
        }
     
        if (!isset($wpdb->auction_dates)) {
            $wpdb->auction_dates = $dates_table;
            $wpdb->tables[] = str_replace($wpdb->prefix, '', $dates_table);
        }
    }
    public function create_countries_table() {
        global $wpdb;
        $countries_table = $wpdb->prefix . self::COUNTRIES_TABLE_PREFIX;
        if($wpdb->get_var("show tables like '$countries_table'") !== $countries_table) {
            $sql = "CREATE TABLE " . $countries_table . " (
                    `short_name` VARCHAR(3) NOT NULL,
                    `name` VARCHAR(100) NOT NULL,
                    PRIMARY KEY (`short_name`)
                    ) ENGINE = InnoDB;
                ";
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            if (!dbDelta($sql)) {
                return;
            }
        }
     
        if (!isset($wpdb->auction_countries)) {
            $wpdb->auction_countries = $countries_table;
            $wpdb->tables[] = str_replace($wpdb->prefix, '', $countries_table);
        }

        // Inserting Denmark in countries
        $wpdb->query(
            "
            INSERT IGNORE INTO $wpdb->auction_countries
            (short_name, name)
            VALUES
            ('DK', 'Danmark')
            "
        );
    }
    public function create_regions_table() {
        global $wpdb;
        $regions_table = $wpdb->prefix . self::REGIONS_TABLE_PREFIX;
        if($wpdb->get_var("show tables like '$regions_table'") !== $regions_table) {
            $sql = "CREATE TABLE " . $regions_table . " (
                    `ID` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `name` VARCHAR(100) NOT NULL,
                    `country` VARCHAR(3) NOT NULL,
                    PRIMARY KEY (`ID`),
                    UNIQUE INDEX `fk_regions_countries1_idx` (`name` ASC, `country` ASC),
                    CONSTRAINT `fk_regions_countries1`
                        FOREIGN KEY (`country`)
                        REFERENCES `$wpdb->auction_countries` (`short_name`)
                        ON DELETE RESTRICT
                        ON UPDATE NO ACTION
                    ) ENGINE = InnoDB;
                ";
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            if (!dbDelta($sql)) {
                return;
            }
        }
     
        if (!isset($wpdb->auction_regions)) {
            $wpdb->auction_regions = $regions_table;
            $wpdb->tables[] = str_replace($wpdb->prefix, '', $regions_table);
        }

        // Inserting regions
        // Bornholm
        // Fyn
        // København
        // Københavns omegn
        // Nordjylland
        // Nordsjælland
        // Sydjylland
        // Vest- og Sydsjælland
        // Vestjylland
        // Østjylland
        // Østsjælland

        $wpdb->query(
            "
            INSERT IGNORE INTO $wpdb->auction_regions
            (country, name)
            VALUES
            ('DK', 'Bornholm'),
            ('DK', 'Fyn'),
            ('DK', 'København'),
            ('DK', 'Københavns omegn'),
            ('DK', 'Nordjylland'),
            ('DK', 'Nordsjælland'),
            ('DK', 'Sydjylland'),
            ('DK', 'Vest- og Sydsjælland'),
            ('DK', 'Vestjylland'),
            ('DK', 'Østjylland'),
            ('DK', 'Østsjælland')
            "
        );
    }
    public function create_zip_codes_table() {
        global $wpdb;
        $zipcodes_table = $wpdb->prefix . self::ZIPCODES_TABLE_PREFIX;
        if($wpdb->get_var("show tables like '$zipcodes_table'") !== $zipcodes_table) {
            $sql = "CREATE TABLE " . $zipcodes_table . " (
                    `zip_code` VARCHAR(15) NOT NULL,
                    `region_id` BIGINT(20) UNSIGNED NOT NULL,
                    `city` VARCHAR(100) NOT NULL,
                    PRIMARY KEY (`zip_code`, `region_id`),
                    INDEX `fk_zip_codes_regions1_idx` (`city` ASC),
                    CONSTRAINT `fk_zip_codes_regions1`
                        FOREIGN KEY (`region_id`)
                        REFERENCES `$wpdb->auction_regions` (`ID`)
                        ON DELETE RESTRICT
                        ON UPDATE NO ACTION
                    ) ENGINE = InnoDB;
                ";
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            if (!dbDelta($sql)) {
                return;
            }
        }
     
        if (!isset($wpdb->auction_zipcodes)) {
            $wpdb->auction_zipcodes = $zipcodes_table;
            $wpdb->tables[] = str_replace($wpdb->prefix, '', $zipcodes_table);
        }
    }
    public function create_address_table() {
        global $wpdb;
        $address_table = $wpdb->prefix . self::ADDRESS_TABLE_PREFIX;
        if($wpdb->get_var("show tables like '$address_table'") !== $address_table) {
            $sql = "CREATE TABLE " . $address_table . " (
                    `ID` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `zip_code` VARCHAR(15) NOT NULL,
                    `region_id` BIGINT(20) UNSIGNED NOT NULL,
                    `street_name` VARCHAR(100) NOT NULL,
                    `street_number` VARCHAR(20) NOT NULL,
                    PRIMARY KEY (`ID`),
                    UNIQUE INDEX `fk_address_zip_codes1_idx` (`zip_code` ASC, `region_id` ASC, `street_name` ASC, `street_number` ASC),
                    CONSTRAINT `fk_address_zip_codes1`
                        FOREIGN KEY (`zip_code` , `region_id`)
                        REFERENCES `$wpdb->auction_zipcodes` (`zip_code` , `region_id`)
                        ON DELETE RESTRICT
                        ON UPDATE NO ACTION
                    ) ENGINE = InnoDB;
                ";
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            if (!dbDelta($sql)) {
                return;
            }
        }

        // TODO: Take a look at triggers. Delete an address if no one is using it (products or users)
     
        if (!isset($wpdb->auction_address)) {
            $wpdb->auction_address = $address_table;
            $wpdb->tables[] = str_replace($wpdb->prefix, '', $address_table);
        }
    }
}

new Auction_Database();
?>