<?php

namespace App\Enums;

enum Language: string
{
    case AB = 'ab'; // Abkhaze
    case AA = 'aa'; // Afar
    case AF = 'af'; // Afrikaans
    case AK = 'ak'; // Akan
    case SQ = 'sq'; // Albanais
    case AM = 'am'; // Amharique
    case AR = 'ar'; // Arabe
    case AN = 'an'; // Aragonais
    case HY = 'hy'; // Arménien
    case AS = 'as'; // Assamais
    case AV = 'av'; // Avar
    case AE = 'ae'; // Avestique
    case AY = 'ay'; // Aymara
    case AZ = 'az'; // Azéri
    case BM = 'bm'; // Bambara
    case BA = 'ba'; // Bachkir
    case EU = 'eu'; // Basque
    case BE = 'be'; // Biélorusse
    case BN = 'bn'; // Bengali
    case BH = 'bh'; // Bihari
    case BI = 'bi'; // Bichelamar
    case BS = 'bs'; // Bosniaque
    case BR = 'br'; // Breton
    case BG = 'bg'; // Bulgare
    case MY = 'my'; // Birman
    case CA = 'ca'; // Catalan
    case CH = 'ch'; // Chamorro
    case CE = 'ce'; // Tchétchène
    case NY = 'ny'; // Chichewa
    case ZH = 'zh'; // Chinois
    case CV = 'cv'; // Tchouvache
    case KW = 'kw'; // Cornique
    case CO = 'co'; // Corse
    case CR = 'cr'; // Cri
    case HR = 'hr'; // Croate
    case CS = 'cs'; // Tchèque
    case DA = 'da'; // Danois
    case DV = 'dv'; // Divehi
    case NL = 'nl'; // Néerlandais
    case DZ = 'dz'; // Dzongkha
    case EN = 'en'; // Anglais
    case EO = 'eo'; // Espéranto
    case ET = 'et'; // Estonien
    case EE = 'ee'; // Ewe
    case FO = 'fo'; // Féroïen
    case FJ = 'fj'; // Fidjien
    case FI = 'fi'; // Finnois
    case FR = 'fr'; // Français
    case FF = 'ff'; // Peul
    case GL = 'gl'; // Galicien
    case KA = 'ka'; // Géorgien
    case DE = 'de'; // Allemand
    case EL = 'el'; // Grec
    case GN = 'gn'; // Guarani
    case GU = 'gu'; // Gujarati
    case HT = 'ht'; // Haïtien
    case HA = 'ha'; // Haoussa
    case HE = 'he'; // Hébreu
    case HZ = 'hz'; // Héréro
    case HI = 'hi'; // Hindi
    case HO = 'ho'; // Hiri motu
    case HU = 'hu'; // Hongrois
    case IA = 'ia'; // Interlingua
    case ID = 'id'; // Indonésien
    case IE = 'ie'; // Occidental
    case GA = 'ga'; // Irlandais
    case IG = 'ig'; // Igbo
    case IK = 'ik'; // Inupiak
    case IO = 'io'; // Ido
    case IS = 'is'; // Islandais
    case IT = 'it'; // Italien
    case IU = 'iu'; // Inuktitut
    case JA = 'ja'; // Japonais
    case JV = 'jv'; // Javanais
    case KL = 'kl'; // Groenlandais
    case KN = 'kn'; // Kannada
    case KR = 'kr'; // Kanouri
    case KS = 'ks'; // Cachemiri
    case KK = 'kk'; // Kazakh
    case KM = 'km'; // Khmer
    case KI = 'ki'; // Kikuyu
    case RW = 'rw'; // Kinyarwanda
    case KY = 'ky'; // Kirghize
    case KV = 'kv'; // Komi
    case KG = 'kg'; // Kikongo
    case KO = 'ko'; // Coréen
    case KU = 'ku'; // Kurde
    case KJ = 'kj'; // Kuanyama
    case LA = 'la'; // Latin
    case LB = 'lb'; // Luxembourgeois
    case LG = 'lg'; // Ganda
    case LI = 'li'; // Limbourgeois
    case LN = 'ln'; // Lingala
    case LO = 'lo'; // Lao
    case LT = 'lt'; // Lituanien
    case LU = 'lu'; // Luba-katanga
    case LV = 'lv'; // Letton
    case GV = 'gv'; // Mannois
    case MK = 'mk'; // Macédonien
    case MG = 'mg'; // Malgache
    case MS = 'ms'; // Malais
    case ML = 'ml'; // Malayalam
    case MT = 'mt'; // Maltais
    case MI = 'mi'; // Maori
    case MR = 'mr'; // Marathi
    case MH = 'mh'; // Marshallais
    case MN = 'mn'; // Mongol
    case NA = 'na'; // Nauruan
    case NV = 'nv'; // Navajo
    case NB = 'nb'; // Norvégien bokmål
    case ND = 'nd'; // Ndébélé du Nord
    case NE = 'ne'; // Népalais
    case NG = 'ng'; // Ndonga
    case NN = 'nn'; // Norvégien nynorsk
    case NO = 'no'; // Norvégien
    case II = 'ii'; // Yi de Sichuan
    case NR = 'nr'; // Ndébélé du Sud
    case OC = 'oc'; // Occitan
    case OJ = 'oj'; // Ojibwé
    case CU = 'cu'; // Slavon d'église
    case OM = 'om'; // Oromo
    case OR = 'or'; // Oriya
    case OS = 'os'; // Ossète
    case PA = 'pa'; // Pendjabi
    case PI = 'pi'; // Pali
    case FA = 'fa'; // Persan
    case PL = 'pl'; // Polonais
    case PS = 'ps'; // Pachto
    case PT = 'pt'; // Portugais
    case QU = 'qu'; // Quechua
    case RM = 'rm'; // Romanche
    case RN = 'rn'; // Rundi
    case RO = 'ro'; // Roumain
    case RU = 'ru'; // Russe
    case SA = 'sa'; // Sanskrit
    case SC = 'sc'; // Sarde
    case SD = 'sd'; // Sindhi
    case SE = 'se'; // Same du Nord
    case SM = 'sm'; // Samoan
    case SG = 'sg'; // Sango
    case SR = 'sr'; // Serbe
    case GD = 'gd'; // Gaélique écossais
    case SN = 'sn'; // Shona
    case SI = 'si'; // Cingalais
    case SK = 'sk'; // Slovaque
    case SL = 'sl'; // Slovène
    case SO = 'so'; // Somali
    case ST = 'st'; // Sotho du Sud
    case ES = 'es'; // Espagnol
    case SU = 'su'; // Soundanais
    case SW = 'sw'; // Swahili
    case SS = 'ss'; // Swati
    case SV = 'sv'; // Suédois
    case TA = 'ta'; // Tamoul
    case TE = 'te'; // Télougou
    case TG = 'tg'; // Tadjik
    case TH = 'th'; // Thaï
    case TI = 'ti'; // Tigrigna
    case BO = 'bo'; // Tibétain
    case TK = 'tk'; // Turkmène
    case TL = 'tl'; // Tagalog
    case TN = 'tn'; // Tswana
    case TO = 'to'; // Tongien
    case TR = 'tr'; // Turc
    case TS = 'ts'; // Tsonga
    case TT = 'tt'; // Tatar
    case TW = 'tw'; // Twi
    case TY = 'ty'; // Tahitien
    case UG = 'ug'; // Ouïghour
    case UK = 'uk'; // Ukrainien
    case UR = 'ur'; // Ourdou
    case UZ = 'uz'; // Ouzbek
    case VE = 've'; // Venda
    case VI = 'vi'; // Vietnamien
    case VO = 'vo'; // Volapük
    case WA = 'wa'; // Wallon
    case CY = 'cy'; // Gallois
    case WO = 'wo'; // Wolof
    case FY = 'fy'; // Frison occidental
    case XH = 'xh'; // Xhosa
    case YI = 'yi'; // Yiddish
    case YO = 'yo'; // Yoruba
    case ZA = 'za'; // Zhuang
    case ZU = 'zu'; // Zoulou

    public function label(): string
    {
        return __('enums/language.'.$this->value);
    }

    public function tagColorVariable(): string
    {
        return '--color-tag-language';
    }

    public function color(): string
    {
        return 'text-tag-language';
    }
}
