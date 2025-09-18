<?php

declare(strict_types=1);

namespace Xgc\Utils;

use Random\Randomizer;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Throwable;

use function strlen;

use const STR_PAD_LEFT;

class StringUtil
{
    public const string LOWER_CASE = 'abcdefghijklmnopqrstuvwxyz';

    public const string NUMBERS = '0123456789';

    public const string SYMBOLS = '!@#$%^&*()_+{}|:<>?';

    public const string UPPER_CASE = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    public static function bytes(
        int $length,
        bool $lowerCase = true,
        bool $upperCase = true,
        bool $numbers = true,
        bool $symbols = false,
    ): string {
        $randomizer = new Randomizer();
        $chars = '';
        if ($lowerCase) {
            $chars .= self::LOWER_CASE;
        }
        if ($upperCase) {
            $chars .= self::UPPER_CASE;
        }
        if ($numbers) {
            $chars .= self::NUMBERS;
        }
        if ($symbols) {
            $chars .= self::SYMBOLS;
        }

        return $randomizer->getBytesFromString(
            $chars,
            $length,
        );
    }

    public static function capitalize(string $input): string
    {
        return ucwords(strtolower($input));
    }

    public static function className(object $class): string
    {
        $path = explode('\\', $class::class);

        return array_pop($path);
    }

    public static function fit(string $value, int $length): string
    {
        $value .= '00000000000000000000000000000000';

        return substr($value, 0, $length);
    }

    public static function fixEmail(string $email): string
    {
        Validate::email('email', $email);

        $parts = explode('@', $email);

        $fixedEmail = '';
        $ignore = false;
        for ($c = 0; $c < strlen($parts[0]); ++$c) {
            $char = $parts[0][$c];
            if ($char === '+') {
                $ignore = true;
            }

            if (!$ignore && $char !== '.') {
                $fixedEmail .= $char;
            }
        }

        return strtolower("{$fixedEmail}@{$parts[1]}");
    }

    public static function localeToIcu(string $locale): string
    {
        return [
            'aa' => 'aa-ET', 'ab' => 'ab-GE', 'ace' => 'ace-ID', 'ach' => 'ach-UG', 'ada' => 'ada-GH',
            'ady' => 'ady-RU', 'ae' => 'ae-IR', 'aeb' => 'aeb-TN', 'af' => 'af-ZA', 'afh' => 'afh-ZA',
            'agq' => 'agq-CM', 'ain' => 'ain-JP', 'ak' => 'ak-GH', 'akk' => 'akk-IQ', 'ale' => 'ale-US',
            'aln' => 'aln-XK', 'alt' => 'alt-RU', 'am' => 'am-ET', 'an' => 'an-ES', 'ang' => 'ang-GB',
            'anp' => 'anp-IN', 'ar' => 'ar-SA', 'arc' => 'arc-IR', 'arn' => 'arn-CL', 'aro' => 'aro-BO',
            'arp' => 'arp-US', 'arq' => 'arq-DZ', 'arw' => 'arw-GY', 'ary' => 'ary-MA', 'arz' => 'arz-EG',
            'as' => 'as-IN', 'asa' => 'asa-TZ', 'ase' => 'ase-US', 'ast' => 'ast-ES', 'av' => 'av-RU',
            'avk' => 'avk-001', 'awa' => 'awa-IN', 'ay' => 'ay-BO', 'az' => 'az-AZ', 'ba' => 'ba-RU',
            'bal' => 'bal-PK', 'ban' => 'ban-ID', 'bar' => 'bar-AT', 'bas' => 'bas-CM', 'bax' => 'bax-CM',
            'bbc' => 'bbc-ID', 'bbj' => 'bbj-CM', 'be' => 'be-BY', 'bej' => 'bej-SD', 'bem' => 'bem-ZM',
            'bew' => 'bew-ID', 'bez' => 'bez-TZ', 'bfd' => 'bfd-CM', 'bfq' => 'bfq-IN', 'bg' => 'bg-BG',
            'bgn' => 'bgn-PK', 'bho' => 'bho-IN', 'bi' => 'bi-VU', 'bik' => 'bik-PH', 'bin' => 'bin-NG',
            'bjn' => 'bjn-ID', 'bkm' => 'bkm-CM', 'bla' => 'bla-CA', 'bm' => 'bm-ML', 'bn' => 'bn-BD',
            'bo' => 'bo-CN', 'bpy' => 'bpy-IN', 'bqi' => 'bqi-IR', 'br' => 'br-FR', 'bra' => 'bra-IN',
            'brh' => 'brh-PK', 'brx' => 'brx-IN', 'bs' => 'bs-BA', 'bss' => 'bss-CM', 'bua' => 'bua-RU',
            'bug' => 'bug-ID', 'bum' => 'bum-CM', 'byn' => 'byn-ER', 'byv' => 'byv-CM', 'ca' => 'ca-ES',
            'cad' => 'cad-US', 'car' => 'car-VE', 'cay' => 'cay-CA', 'cch' => 'cch-NG', 'ccp' => 'ccp-BD',
            'ce' => 'ce-RU', 'ceb' => 'ceb-PH', 'cf' => 'cf-CF', 'cgg' => 'cgg-UG', 'ch' => 'ch-GU',
            'chb' => 'chb-CO', 'chg' => 'chg-TM', 'chk' => 'chk-FM', 'chm' => 'chm-RU', 'chn' => 'chn-US',
            'cho' => 'cho-US', 'chp' => 'chp-CA', 'chr' => 'chr-US', 'chy' => 'chy-US', 'ckb' => 'ckb-IQ',
            'co' => 'co-FR', 'cop' => 'cop-EG', 'cps' => 'cps-PH', 'cr' => 'cr-CA', 'crh' => 'crh-UA',
            'crs' => 'crs-SC', 'cs' => 'cs-CZ', 'csb' => 'csb-PL', 'cu' => 'cu-RU', 'cv' => 'cv-RU',
            'cy' => 'cy-GB', 'da' => 'da-DK', 'dak' => 'dak-US', 'dar' => 'dar-RU', 'dav' => 'dav-KE',
            'de' => 'de-DE', 'del' => 'del-US', 'den' => 'den-CA', 'dgr' => 'dgr-CA', 'din' => 'din-SS',
            'dje' => 'dje-NE', 'doi' => 'doi-IN', 'dsb' => 'dsb-DE', 'dtp' => 'dtp-MY', 'dua' => 'dua-CM',
            'dum' => 'dum-NL', 'dv' => 'dv-MV', 'dyo' => 'dyo-SN', 'dyu' => 'dyu-BF', 'dz' => 'dz-BT',
            'dzg' => 'dzg-CD', 'ebu' => 'ebu-KE', 'ee' => 'ee-GH', 'efi' => 'efi-NG', 'egy' => 'egy-EG',
            'eka' => 'eka-KE', 'el' => 'el-GR', 'elx' => 'elx-ES', 'en' => 'en-US', 'enm' => 'enm-GB',
            'eo' => 'eo-001', 'es' => 'es-ES', 'esu' => 'esu-US', 'et' => 'et-EE', 'eu' => 'eu-ES',
            'ewo' => 'ewo-CM', 'ext' => 'ext-ES', 'fa' => 'fa-IR', 'fan' => 'fan-GQ', 'fat' => 'fat-GH',
            'ff' => 'ff-SN', 'fi' => 'fi-FI', 'fil' => 'fil-PH', 'fit' => 'fit-FI', 'fj' => 'fj-FJ',
            'fo' => 'fo-FO', 'fon' => 'fon-BJ', 'fr' => 'fr-FR', 'frc' => 'frc-US', 'frm' => 'frm-FR',
            'fro' => 'fro-FR', 'frp' => 'frp-FR', 'frr' => 'frr-DE', 'frs' => 'frs-DE', 'fur' => 'fur-IT',
            'fy' => 'fy-NL', 'ga' => 'ga-IE', 'gaa' => 'gaa-GH', 'gay' => 'gay-ID', 'gba' => 'gba-CF',
            'gbz' => 'gbz-IR', 'gd' => 'gd-GB', 'gez' => 'gez-ET', 'gil' => 'gil-KI', 'gl' => 'gl-ES',
            'glk' => 'glk-IR', 'gmh' => 'gmh-DE', 'gn' => 'gn-PY', 'gom' => 'gom-IN', 'gon' => 'gon-IN',
            'gor' => 'gor-ID', 'got' => 'got-UA', 'grb' => 'grb-LR', 'grc' => 'grc-GR', 'gsw' => 'gsw-CH',
            'gu' => 'gu-IN', 'guc' => 'guc-CO', 'gur' => 'gur-GH', 'guz' => 'guz-KE', 'gv' => 'gv-IM',
            'gwi' => 'gwi-CA', 'ha' => 'ha-NG', 'hai' => 'hai-CA', 'hak' => 'hak-TW', 'haw' => 'haw-US',
            'he' => 'he-IL', 'hi' => 'hi-IN', 'hif' => 'hif-FJ', 'hil' => 'hil-PH', 'hit' => 'hit-TR',
            'hmn' => 'hmn-CN', 'ho' => 'ho-PG', 'hr' => 'hr-HR', 'hsb' => 'hsb-DE', 'hsn' => 'hsn-CN',
            'ht' => 'ht-HT', 'hu' => 'hu-HU', 'hup' => 'hup-US', 'hy' => 'hy-AM', 'hz' => 'hz-NA',
            'ia' => 'ia-001', 'iba' => 'iba-MY', 'ibb' => 'ibb-NG', 'id' => 'id-ID', 'ie' => 'ie-001',
            'ig' => 'ig-NG', 'ii' => 'ii-CN', 'ik' => 'ik-US', 'ilo' => 'ilo-PH', 'inh' => 'inh-RU',
            'io' => 'io-001', 'is' => 'is-IS', 'it' => 'it-IT', 'iu' => 'iu-CA', 'izh' => 'izh-RU',
            'ja' => 'ja-JP', 'jam' => 'jam-JM', 'jbo' => 'jbo-001', 'jgo' => 'jgo-CM', 'ji' => 'ji-UA',
            'jmc' => 'jmc-TZ', 'jpr' => 'jpr-IL', 'jrb' => 'jrb-IL', 'jut' => 'jut-DK', 'jv' => 'jv-ID',
            'ka' => 'ka-GE', 'kaa' => 'kaa-UZ', 'kab' => 'kab-DZ', 'kac' => 'kac-MM', 'kaj' => 'kaj-NG',
            'kam' => 'kam-KE', 'kaw' => 'kaw-ID', 'kbd' => 'kbd-RU', 'kbl' => 'kbl-AF', 'kcg' => 'kcg-NG',
            'kde' => 'kde-TZ', 'kea' => 'kea-CV', 'ken' => 'ken-CM', 'kfo' => 'kfo-CI', 'kg' => 'kg-CD',
            'kgp' => 'kgp-BR', 'kha' => 'kha-IN', 'kho' => 'kho-CN', 'ki' => 'ki-KE', 'kiu' => 'kiu-TR',
            'kj' => 'kj-NA', 'kk' => 'kk-KZ', 'kkj' => 'kkj-CM', 'kl' => 'kl-GL', 'kln' => 'kln-KE',
            'km' => 'km-KH', 'kmb' => 'kmb-AO', 'kn' => 'kn-IN', 'ko' => 'ko-KR', 'koi' => 'koi-RU',
            'kok' => 'kok-IN', 'kos' => 'kos-FM', 'kpe' => 'kpe-LR', 'kr' => 'kr-NG', 'krc' => 'krc-RU',
            'kri' => 'kri-SL', 'krj' => 'krj-PH', 'krl' => 'krl-RU', 'kru' => 'kru-IN', 'ks' => 'ks-IN',
            'ksb' => 'ksb-TZ', 'ksf' => 'ksf-CM', 'ksh' => 'ksh-DE', 'ku' => 'ku-TR', 'kum' => 'kum-RU',
            'kut' => 'kut-US', 'kv' => 'kv-RU', 'kw' => 'kw-GB', 'ky' => 'ky-KG', 'la' => 'la-VA',
            'lad' => 'lad-IL', 'lag' => 'lag-TZ', 'lah' => 'lah-PK', 'lam' => 'lam-ZM', 'lb' => 'lb-LU',
            'lbe' => 'lbe-RU', 'lbw' => 'lbw-ID', 'lcm' => 'lcm-KM', 'lcp' => 'lcp-CN', 'ldi' => 'ldi-CG',
            'lez' => 'lez-RU', 'lfn' => 'lfn-001', 'lg' => 'lg-UG', 'li' => 'li-NL', 'lij' => 'lij-IT',
            'liv' => 'liv-LV', 'lkt' => 'lkt-US', 'lmo' => 'lmo-IT', 'ln' => 'ln-CD', 'lo' => 'lo-LA',
            'lol' => 'lol-CD', 'loz' => 'loz-ZM', 'lrc' => 'lrc-IR', 'lt' => 'lt-LT', 'ltg' => 'ltg-LV',
            'lu' => 'lu-CD', 'lua' => 'lua-CD', 'lui' => 'lui-US', 'lun' => 'lun-ZM', 'luo' => 'luo-KE',
            'lus' => 'lus-IN', 'luy' => 'luy-KE', 'lv' => 'lv-LV', 'lzh' => 'lzh-CN', 'lzz' => 'lzz-TR',
            'mad' => 'mad-ID', 'maf' => 'maf-CM', 'mag' => 'mag-IN', 'mai' => 'mai-IN', 'mak' => 'mak-ID',
            'man' => 'man-GN', 'map' => 'map-PH', 'mas' => 'mas-KE', 'mde' => 'mde-ZM', 'mdf' => 'mdf-RU',
            'mdh' => 'mdh-PH', 'mdr' => 'mdr-ID', 'men' => 'men-SL', 'mer' => 'mer-KE', 'mfe' => 'mfe-MU',
            'mg' => 'mg-MG', 'mga' => 'mga-IE', 'mgh' => 'mgh-MZ', 'mgo' => 'mgo-CM', 'mh' => 'mh-MH',
            'mi' => 'mi-NZ', 'mic' => 'mic-CA', 'min' => 'min-ID', 'mis' => 'mis-MM', 'mk' => 'mk-MK',
            'mkd' => 'mkd-CD', 'ml' => 'ml-IN', 'mn' => 'mn-MN', 'mnc' => 'mnc-CN', 'mni' => 'mni-IN',
            'mns' => 'mns-RU', 'mo' => 'mo-RO', 'moh' => 'moh-CA', 'mos' => 'mos-BF', 'mr' => 'mr-IN',
            'mrj' => 'mrj-RU', 'ms' => 'ms-MY', 'mt' => 'mt-MT', 'mua' => 'mua-CM', 'mul' => 'mul-001',
            'mus' => 'mus-US', 'mwl' => 'mwl-PT', 'mwr' => 'mwr-IN', 'mwv' => 'mwv-ID', 'my' => 'my-MM',
            'mye' => 'mye-MM', 'myv' => 'myv-RU', 'mzn' => 'mzn-IR', 'na' => 'na-NR', 'nan' => 'nan-TW',
            'nap' => 'nap-IT', 'naq' => 'naq-NA', 'nb' => 'nb-NO', 'nd' => 'nd-ZW', 'nds' => 'nds-DE',
            'ne' => 'ne-NP', 'new' => 'new-NP', 'ng' => 'ng-NA', 'nia' => 'nia-ID', 'niu' => 'niu-NU',
            'njo' => 'njo-IN', 'nl' => 'nl-NL', 'nmg' => 'nmg-CM', 'nn' => 'nn-NO', 'nnh' => 'nnh-CM',
            'no' => 'no-NO', 'nog' => 'nog-RU', 'non' => 'non-NO', 'nqo' => 'nqo-GN', 'nr' => 'nr-ZA',
            'nso' => 'nso-ZA', 'nus' => 'nus-SS', 'nv' => 'nv-US', 'nwc' => 'nwc-NP', 'ny' => 'ny-MW',
            'nym' => 'nym-TZ', 'nyn' => 'nyn-UG', 'nyo' => 'nyo-UG', 'nzi' => 'nzi-GH', 'oc' => 'oc-FR',
            'oj' => 'oj-CA', 'om' => 'om-ET', 'or' => 'or-IN', 'os' => 'os-GE', 'osa' => 'osa-US',
            'ota' => 'ota-TR', 'pa' => 'pa-IN', 'pag' => 'pag-PH', 'pal' => 'pal-IR', 'pam' => 'pam-PH',
            'pap' => 'pap-AW', 'pau' => 'pau-PW', 'pcd' => 'pcd-FR', 'pcm' => 'pcm-NG', 'pdc' => 'pdc-US',
            'pdt' => 'pdt-CA', 'peo' => 'peo-IR', 'pfl' => 'pfl-DE', 'phn' => 'phn-LB', 'pi' => 'pi-IN',
            'pl' => 'pl-PL', 'pms' => 'pms-IT', 'pnt' => 'pnt-GR', 'pon' => 'pon-FM', 'prg' => 'prg-001',
            'pro' => 'pro-FR', 'ps' => 'ps-AF', 'pt' => 'pt-PT', 'qu' => 'qu-PE', 'quc' => 'quc-GT',
            'qug' => 'qug-EC', 'raj' => 'raj-IN', 'rap' => 'rap-CL', 'rar' => 'rar-CK', 'rcf' => 'rcf-RE',
            'rej' => 'rej-ID', 'rel' => 'rel-001', 'ren' => 'ren-CA', 'rgn' => 'rgn-IT', 'rif' => 'rif-MA',
            'rm' => 'rm-CH', 'rn' => 'rn-BI', 'ro' => 'ro-RO', 'rof' => 'rof-TZ', 'rom' => 'rom-RO',
            'rtm' => 'rtm-FJ', 'ru' => 'ru-RU', 'rue' => 'rue-UA', 'rug' => 'rug-SB', 'rup' => 'rup-MK',
            'rw' => 'rw-RW', 'rwk' => 'rwk-TZ', 'sa' => 'sa-IN', 'sad' => 'sad-TD', 'saf' => 'saf-GH',
            'sah' => 'sah-RU', 'sam' => 'sam-IL', 'saq' => 'saq-KE', 'sas' => 'sas-ID', 'sat' => 'sat-IN',
            'saz' => 'saz-IN', 'sba' => 'sba-ZA', 'sbp' => 'sbp-TZ', 'sc' => 'sc-IT', 'scn' => 'scn-IT',
            'sco' => 'sco-GB', 'sd' => 'sd-PK', 'sdc' => 'sdc-IT', 'sdh' => 'sdh-IR', 'se' => 'se-NO',
            'see' => 'see-US', 'seh' => 'seh-MZ', 'sei' => 'sei-MX', 'sel' => 'sel-RU', 'ses' => 'ses-ML',
            'sg' => 'sg-CF', 'sga' => 'sga-IE', 'sgs' => 'sgs-LT', 'sh' => 'sh-BA', 'shi' => 'shi-MA',
            'shn' => 'shn-MM', 'shs' => 'shs-CA', 'si' => 'si-LK', 'sid' => 'sid-ET', 'sio' => 'sio-US',
            'sit' => 'sit-IN', 'sk' => 'sk-SK', 'sl' => 'sl-SI', 'sli' => 'sli-PL', 'sly' => 'sly-ID',
            'sm' => 'sm-WS', 'sma' => 'sma-SE', 'smj' => 'smj-NO', 'smn' => 'smn-FI', 'sms' => 'sms-FI',
            'sn' => 'sn-ZW', 'snk' => 'snk-SN', 'so' => 'so-SO', 'sog' => 'sog-UZ', 'sok' => 'sok-CD',
            'sq' => 'sq-AL', 'sr' => 'sr-RS', 'srn' => 'srn-SR', 'srr' => 'srr-SN', 'ss' => 'ss-ZA',
            'ssy' => 'ssy-ER', 'st' => 'st-LS', 'stq' => 'stq-DE', 'su' => 'su-ID', 'suk' => 'suk-TZ',
            'sus' => 'sus-GN', 'suv' => 'suv-PG', 'sv' => 'sv-SE', 'sw' => 'sw-KE', 'swb' => 'swb-YT',
            'swc' => 'swc-CD', 'swg' => 'swg-DE', 'swv' => 'swv-IN', 'sxn' => 'sxn-PF', 'sxu' => 'sxu-DE',
            'syl' => 'syl-BD', 'syr' => 'syr-IQ', 'szl' => 'szl-PL', 'ta' => 'ta-IN', 'tcy' => 'tcy-IN',
            'tdd' => 'tdd-CN', 'te' => 'te-IN', 'tem' => 'tem-SL', 'teo' => 'teo-KE', 'ter' => 'ter-NG',
            'tet' => 'tet-TL', 'tg' => 'tg-TJ', 'th' => 'th-TH', 'ti' => 'ti-ER', 'tig' => 'tig-ER',
            'tiv' => 'tiv-NG', 'tk' => 'tk-TM', 'tkl' => 'tkl-TK', 'tkr' => 'tkr-AZ', 'tl' => 'tl-PH',
            'tlh' => 'tlh-001', 'tli' => 'tli-US', 'tly' => 'tly-AZ', 'tmh' => 'tmh-NE', 'tn' => 'tn-BW',
            'to' => 'to-TO', 'tog' => 'tog-MW', 'toi' => 'toi-TZ', 'tok' => 'tok-001', 'tol' => 'tol-HN',
            'tpi' => 'tpi-PG', 'tr' => 'tr-TR', 'tru' => 'tru-TR', 'trv' => 'trv-TW', 'ts' => 'ts-ZA',
            'tsd' => 'tsd-GR', 'tsi' => 'tsi-CA', 'tt' => 'tt-RU', 'ttm' => 'ttm-CA', 'ttt' => 'ttt-AZ',
            'tum' => 'tum-MW', 'tvl' => 'tvl-TV', 'tw' => 'tw-GH', 'twq' => 'twq-NE', 'ty' => 'ty-PF',
            'tyv' => 'tyv-RU', 'tzm' => 'tzm-MA', 'udm' => 'udm-RU', 'ug' => 'ug-CN', 'uga' => 'uga-SY',
            'uk' => 'uk-UA', 'umb' => 'umb-AO', 'und' => 'und-001', 'ur' => 'ur-PK', 'uz' => 'uz-UZ',
            'vai' => 'vai-LR', 've' => 've-ZA', 'vec' => 'vec-IT', 'vep' => 'vep-RU', 'vi' => 'vi-VN',
            'vls' => 'vls-BE', 'vmf' => 'vmf-DE', 'vo' => 'vo-001', 'vot' => 'vot-RU', 'vro' => 'vro-EE',
            'vun' => 'vun-TZ', 'wa' => 'wa-BE', 'wae' => 'wae-CH', 'wal' => 'wal-ET', 'war' => 'war-PH',
            'was' => 'was-US', 'wbp' => 'wbp-AU', 'wbq' => 'wbq-IN', 'wbr' => 'wbr-IN', 'wls' => 'wls-WF',
            'wni' => 'wni-KM', 'wo' => 'wo-SN', 'wuu' => 'wuu-CN', 'xal' => 'xal-RU', 'xh' => 'xh-ZA',
            'xmf' => 'xmf-GE', 'xog' => 'xog-UG', 'yao' => 'yao-MW', 'yap' => 'yap-FM', 'yav' => 'yav-CM',
            'ybb' => 'ybb-CM', 'yi' => 'yi-001', 'yo' => 'yo-NG', 'yom' => 'yom-CD', 'yrl' => 'yrl-BR',
            'yue' => 'yue-HK', 'za' => 'za-CN', 'zag' => 'zag-SD', 'zap' => 'zap-MX', 'zbl' => 'zbl-001',
            'zea' => 'zea-NL', 'zen' => 'zen-NG', 'zgh' => 'zgh-MA', 'zh' => 'zh-CN', 'zu' => 'zu-ZA',
            'zun' => 'zun-US', 'zza' => 'zza-TR',

            // Complex locales - add as is if they already contain underscores
            'zh_Hans' => 'zh-Hans-CN',
            'zh_Hant' => 'zh-Hant-TW',
            'zh_Hans_CN' => 'zh-Hans-CN',
            'zh_Hans_HK' => 'zh-Hans-HK',
            'zh_Hans_MO' => 'zh-Hans-MO',
            'zh_Hans_SG' => 'zh-Hans-SG',
            'zh_Hant_HK' => 'zh-Hant-HK',
            'zh_Hant_MO' => 'zh-Hant-MO',
            'zh_Hant_TW' => 'zh-Hant-TW',
            'sr_Cyrl' => 'sr-Cyrl-RS',
            'sr_Latn' => 'sr-Latn-RS',
            'uz_Arab' => 'uz-Arab-AF',
            'uz_Cyrl' => 'uz-Cyrl-UZ',
            'uz_Latn' => 'uz-Latn-UZ'
        ][$locale] ?? 'es-ES';
    }

    public static function randomColor(): string
    {
        try {
            return '#' . str_pad(dechex(random_int(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
        } catch (Throwable) {
            return '#000000';
        }
    }

    public static function slug(string $input, bool $trimFirst = true): string
    {
        if ($trimFirst) {
            $input = self::trim($input);
        }

        $slugger = new AsciiSlugger();

        return strtolower($slugger->slug($input)->toString());
    }

    public static function trim(string $input): string
    {
        $result = preg_replace('/\s+/', ' ', trim($input)) ?? '';

        if ($result === ' ') {
            return '';
        }

        return $result;
    }

    public static function uuid4(): string
    {
        return RandomUtil::uuid4();
    }
}
