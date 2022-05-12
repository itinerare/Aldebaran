<!--- BEGIN HEADER -->
# Changelog

All notable changes to this project will be documented in this file.
<!--- END HEADER -->

## [2.1.1](https://github.com/itinerare/Aldebaran/compare/v2.1.0...v2.1.1) (2022-05-12)

---

## [2.1.0](https://github.com/itinerare/Aldebaran/compare/v2.0.0...v2.1.0) (2022-05-12)
### Features


##### Tests

* Add non-page blurb to page view tests ([31ea44](https://github.com/itinerare/Aldebaran/commit/31ea44bb0e1d9b12769cb782b5ce6ce5eea07279))


---

## [2.0.0](https://github.com/itinerare/Aldebaran/compare/6a60caf6d49342946d8b6b7595266d8edf711dda...v2.0.0) (2022-05-12)
### ⚠ BREAKING CHANGES


##### Commissions

* Drop price data columns ([44029a](https://github.com/itinerare/Aldebaran/commit/44029ac8219848ca025520da531e60cf9b4f187b))
* Move comm type show examples to column ([730540](https://github.com/itinerare/Aldebaran/commit/7305405c0c28926d0d03adb13c1f2ccbfabb3253))

### Features

* Add 'all pieces' feed ([c8cd60](https://github.com/itinerare/Aldebaran/commit/c8cd605a8cbc0cb1cdece32123b478ba30651b26))
* Add feed index ([6024be](https://github.com/itinerare/Aldebaran/commit/6024be0414f2bab25c78b0e2fa1b84760834bf39))
* Add rss feeds ([532e5b](https://github.com/itinerare/Aldebaran/commit/532e5bfa1619d67fbb031fbe588049e5fcb082d4))
* Update exception handling to add errors to session ([bd1e49](https://github.com/itinerare/Aldebaran/commit/bd1e497fcdc5097a58a389aab0c31233a86c8193))
* Update footer with project link and version ([1fc443](https://github.com/itinerare/Aldebaran/commit/1fc443a88fb4ca0793ff2ebb21e161b5d94ab381))

##### Backups

* Set up automatic backups ([e94e7f](https://github.com/itinerare/Aldebaran/commit/e94e7fdf3894d1adc84a724027602b800cf98677))

##### Commands

* Add changelog generation command ([23a6d2](https://github.com/itinerare/Aldebaran/commit/23a6d2c86248096a65e2cb65d68dcb7332b19874))
* Add setup and update commands ([5a5c68](https://github.com/itinerare/Aldebaran/commit/5a5c6820fd46d909388548352ea491a6e3f2f23b))
* Update dummy commissioner command ([4c6ea9](https://github.com/itinerare/Aldebaran/commit/4c6ea9d12d67b6d4d1f68574700ee32fdeb34ac9))

##### Commissions

* Add commission ID to ledger listings ([74b087](https://github.com/itinerare/Aldebaran/commit/74b087e98f2c02c6e84e20668174bce840fe1d94))
* Add pos-in-queue indicator ([b45b1a](https://github.com/itinerare/Aldebaran/commit/b45b1a5520ba399963d7135b1afefe6d3de4749f))
* Adjust ledger for payment dates ([89b7ad](https://github.com/itinerare/Aldebaran/commit/89b7adcc6f8ffbfb4a4830cc21eb842329093f25))
* Fee/better multi-payments ([e9fabc](https://github.com/itinerare/Aldebaran/commit/e9fabc9fa22fd29b427794f8ab9c3a111b6c01ff))
* Intl fee support ([02d7a4](https://github.com/itinerare/Aldebaran/commit/02d7a4004d293523c3fc7de4952e99984df8d113))
* Move comms setting to config ([9acc26](https://github.com/itinerare/Aldebaran/commit/9acc26bf0c5becd5ea9913813c17cc8d05c20748))
* Move payments to own model ([d44960](https://github.com/itinerare/Aldebaran/commit/d449603e2fee92c5651d053308b7154e52b1b604))
* Rework ledger display ([3f744d](https://github.com/itinerare/Aldebaran/commit/3f744d387068236594f44859554c51340fcfa01b))

##### Comms

* Add commission status message(s) ([966bff](https://github.com/itinerare/Aldebaran/commit/966bfff8b697561a1992880c424ba6f8a5fa5e74))
* Add tip tracking ([f6ecce](https://github.com/itinerare/Aldebaran/commit/f6ecce4c933c3995c901e72da298f6c78596ce8b))
* Optional mail notif on comm request ([8be991](https://github.com/itinerare/Aldebaran/commit/8be991df528d81ff44edef3af588380239888824))

##### Factories

* Update commission payment factory to include paidAt in paid state ([8058ea](https://github.com/itinerare/Aldebaran/commit/8058eaf9df5217965efeefc94fc369acc982bb12))

##### Layout

* Add full-width layout option ([476bee](https://github.com/itinerare/Aldebaran/commit/476bee0ced3d31b05c1e732fc2021b0a43e1bee0))
* Add navigation config settings ([44fcb1](https://github.com/itinerare/Aldebaran/commit/44fcb1e6e877f0edaf9d65d8d1bc3d1935d820d5))

##### Programs

* Change text to media/programs ([c8ac37](https://github.com/itinerare/Aldebaran/commit/c8ac376328ec81148a81b26d5be9e71dc882ecfb))

##### Tags

* Prevent tag deletion if a comm type uses it ([3afc14](https://github.com/itinerare/Aldebaran/commit/3afc1480b68f289b73dc963cbe3d10a0deeae4e3))

##### Tests

* Add comm form submission tests ([aa3748](https://github.com/itinerare/Aldebaran/commit/aa374883ab8b3e08c8aaafa6d572a41885eb7253))
* Add commission category tests with data ([a61857](https://github.com/itinerare/Aldebaran/commit/a618571de75d67ab311641fb4b8e6744792b4e96))
* Add commission type tests, factory ([9dea61](https://github.com/itinerare/Aldebaran/commit/9dea610212c076d7cc8962faeffdebed2360017e))
* Add commission update tests ([8cf323](https://github.com/itinerare/Aldebaran/commit/8cf3238e9b8b5c174fed8f4dd5b973a46b32902c))
* Add comm queue view tests ([826b4e](https://github.com/itinerare/Aldebaran/commit/826b4e674e67e5cd9a8246ad6fa88d52df95d73a))
* Add comm type info tests ([ad5fd4](https://github.com/itinerare/Aldebaran/commit/ad5fd439c02bc0ba4cabefe1ceb12e01944d9fba))
* Add fee calculation tests ([2c9954](https://github.com/itinerare/Aldebaran/commit/2c99540990ca35d3856b4c89798ac4aa0a2fa874))
* Add hidden() function to factories, adjust tests appropriately ([f615dc](https://github.com/itinerare/Aldebaran/commit/f615dc026a248c278c0ee846b92738b6edbafd3a))
* Add ledger view test ([f49ab1](https://github.com/itinerare/Aldebaran/commit/f49ab1e99ceeba0b3c3ad00500ce9cbaf57b32b2))
* Add piece image create tests ([dc7d12](https://github.com/itinerare/Aldebaran/commit/dc7d120b2f4472609c6754cd13f2495a076f78b7))
* Add piece view to admin comm view tests ([17c6bb](https://github.com/itinerare/Aldebaran/commit/17c6bb16129af0322a29450fcd8d4b773d210ec0))
* Add piece view to comm view tests ([ee6e04](https://github.com/itinerare/Aldebaran/commit/ee6e041ca3e8a329535acfb95a7face89b3164cf))
* Add slot-related tests to commission form tests ([623260](https://github.com/itinerare/Aldebaran/commit/6232604ffce7b7c0534169b21834b3458d7c5c75))
* Add slot-related tests to commission state edit tests ([3738ef](https://github.com/itinerare/Aldebaran/commit/3738ef1453817f9cdb26e26a75b82a8d1e78fc2b))
* Add tests re removing optional things ([47ace6](https://github.com/itinerare/Aldebaran/commit/47ace66799f5283987057bf2984a96511fd0b7c2))
* Add visibility admin changelog tests ([4afdc4](https://github.com/itinerare/Aldebaran/commit/4afdc462835110f8dc942c9dea98d07f3a78f952))
* Admin comm view and state update tests ([8a9aa9](https://github.com/itinerare/Aldebaran/commit/8a9aa9edd374d715c360d73ae5d014d0270e813f))
* Admin function tests ([b703cc](https://github.com/itinerare/Aldebaran/commit/b703ccaa955e2b1f996dbc665987947e72a1a7ab))
* Admin index with queue and ledger view tests ([603ac3](https://github.com/itinerare/Aldebaran/commit/603ac355c6b3235806c7813a974957c8c6b11e40))
* Admin site images tests ([475c80](https://github.com/itinerare/Aldebaran/commit/475c800695f6263c500849d72178f04240ba1136))
* Auth login tests ([509ff2](https://github.com/itinerare/Aldebaran/commit/509ff28ac8d870318689ae4b394b3dd7beedc525))
* Basic access tests, user factory ([0f4367](https://github.com/itinerare/Aldebaran/commit/0f4367edd21eaa2709162a86811f856ef37fc384))
* Basic gallery data piece tests, factory ([cdcda3](https://github.com/itinerare/Aldebaran/commit/cdcda387be8c4f66eccc38dc60f3456dcc97e39e))
* Changelog view tests ([43719b](https://github.com/itinerare/Aldebaran/commit/43719ba8be6a041dd520bc02ac60275aee56a649))
* Commission category tests, factory ([f8aea1](https://github.com/itinerare/Aldebaran/commit/f8aea130d22a58d55944ef6e9d443548eb1cc642))
* Commission class tests, factory ([265494](https://github.com/itinerare/Aldebaran/commit/2654946a0641078d31ed96cecf8833dd2e7169f8))
* Commission form view tests ([30d15b](https://github.com/itinerare/Aldebaran/commit/30d15be925708224e47e6f25dbab3529d4736c83))
* Commission info access tests ([c09d03](https://github.com/itinerare/Aldebaran/commit/c09d0381a112550e88ce840b3af60ef76a7b91ce))
* Commission view tests ([ce0d81](https://github.com/itinerare/Aldebaran/commit/ce0d81f527fec5995790531c79c8dfdea5cb0d9c))
* Extend commission category delete tests ([dfb0d8](https://github.com/itinerare/Aldebaran/commit/dfb0d8744d275cb17a43c18ae59f7fa21b03fbfe))
* Extend commission create/view tests ([e3b42e](https://github.com/itinerare/Aldebaran/commit/e3b42e10d762cc4ca062f5f0dafac9243ff27925))
* Extend commission view tests ([cc4959](https://github.com/itinerare/Aldebaran/commit/cc4959ea64b379f3c907ad1384707da3775fa1fa))
* Extend comm type admin index tests ([41df26](https://github.com/itinerare/Aldebaran/commit/41df26f1ea0dd35240005afbc1b15b509ad197e6))
* Extend piece admin index tests ([4706d6](https://github.com/itinerare/Aldebaran/commit/4706d67e4197d6adf4a29f0ab0fc3f2d693445b8))
* Extend program delete test ([726fed](https://github.com/itinerare/Aldebaran/commit/726fedf3070c1ff2464a4f5d2bcd3b06ff4a5a49))
* Extend tag delete test ([19e718](https://github.com/itinerare/Aldebaran/commit/19e7188c54efe763b36d5b726743909102ffe39d))
* Extend tag delete tests ([6d6590](https://github.com/itinerare/Aldebaran/commit/6d6590b2e7f90b57a7d3ebc649bdde96cafd04ea))
* Feed view tests ([233003](https://github.com/itinerare/Aldebaran/commit/23300363e19a2077ceb56323fe60cffdad118989))
* Gallery access tests ([2f9d81](https://github.com/itinerare/Aldebaran/commit/2f9d813dfcbcde8bd8d1511571608cdb15d6ca3f))
* Gallery data program tests, factory ([e69a72](https://github.com/itinerare/Aldebaran/commit/e69a72a4f201efa90c246201ea1d40168050ccea))
* Gallery data projects tests, factory ([6c1f9f](https://github.com/itinerare/Aldebaran/commit/6c1f9fbc607ef53da84e67164b3f7f091db374e9))
* Gallery data tag tests, factory ([abe913](https://github.com/itinerare/Aldebaran/commit/abe913c357351dc083dc65db989d2ee34bbf47dc))
* Page view tests ([505d60](https://github.com/itinerare/Aldebaran/commit/505d60a3ae7340dcc23fe023558b662543d3531a))
* Piece image info tests, factory ([446ac1](https://github.com/itinerare/Aldebaran/commit/446ac1bfa5a74b5e86add2125dab4ca6e7a8dd4e))
* Piece tag and program tests, factories ([04d155](https://github.com/itinerare/Aldebaran/commit/04d155cbbc13a88cfd795df7b651b268c015fd0c))
* Set up and ensure very basic test functioning ([ddc350](https://github.com/itinerare/Aldebaran/commit/ddc3508864e1e964779820d8346753d22ab16e88))
* Update admin function tests ([aefe20](https://github.com/itinerare/Aldebaran/commit/aefe20ab509447ca0d15ef88f1f8c0fd4b2d65c2))
* Update admin tests to check for errors ([7fe959](https://github.com/itinerare/Aldebaran/commit/7fe959f5eefbd11fa37b4bb383cbcff8bac2abb0))
* Update commission data tests to check for errors ([d00728](https://github.com/itinerare/Aldebaran/commit/d007287be34f58d6a6edf94e7e68ae1c6fabf85f))
* Update data gallery tests, admin test ([7a73cb](https://github.com/itinerare/Aldebaran/commit/7a73cbfa772ed0a1ffd1c00303ca555c359c4a44))
* Update gallery data tests to check for errors ([b89dcb](https://github.com/itinerare/Aldebaran/commit/b89dcba804f7d1d89c1fd73a952cfa48bb155b09))

### Bug Fixes

* Better handling for mid-setup index page ([8ce7a1](https://github.com/itinerare/Aldebaran/commit/8ce7a15659e47b12b4cc4a7165e34e20d26c7199))
* Errant JS ([1a5ba7](https://github.com/itinerare/Aldebaran/commit/1a5ba7d6bf430ad6e2b89ee7475d1962f1f73590))
* Error installing packages without DB ([903639](https://github.com/itinerare/Aldebaran/commit/903639cccbf46d08e8c0092260d31ca28f641bf8))
* Error performing initial setup ([8d23c2](https://github.com/itinerare/Aldebaran/commit/8d23c2d5e53f71da123e656e8f3176f967274c50))
* Errors with cost data migration command ([8109c6](https://github.com/itinerare/Aldebaran/commit/8109c67b2ce46209f87a115dc542ca2c4233814d))
* Error viewing admin index ([75cd9c](https://github.com/itinerare/Aldebaran/commit/75cd9c273c452b8532a341d6ac26bc966c4d26f0))
* Pagination issues ([0d7fa2](https://github.com/itinerare/Aldebaran/commit/0d7fa205e67dc379df73a2d3c48f3c7327d6cdbe))
* Remove unnecessary json encoding ([8d6978](https://github.com/itinerare/Aldebaran/commit/8d69780b8fdb1420cedf0fae66febf5dd42da828))
* Tiny editor doesn't init ([caaf56](https://github.com/itinerare/Aldebaran/commit/caaf56466add8df55bd8ab099d8a9c4260ba8cd4))

##### Admin

* Error viewing index with no commission classes ([996ca1](https://github.com/itinerare/Aldebaran/commit/996ca1804c1fcb98e9125562e5c805c018d873b3))
* Move Eloquent calls to controller ([fa6d42](https://github.com/itinerare/Aldebaran/commit/fa6d42a8757d10fa09b6f210c55f51c2ee381026))

##### Commands

* Error creating admin user ([91b918](https://github.com/itinerare/Aldebaran/commit/91b9188eadffcfbab973ad93b14b3c279f702993))

##### Commissions

* Add extra check to cost migrate command, fix formatting ([d82f3c](https://github.com/itinerare/Aldebaran/commit/d82f3cfcad1e53833219c91837c29e8359a74021))
* Add safety check to new comm view ([4bcb99](https://github.com/itinerare/Aldebaran/commit/4bcb990d971287e961e83265c721644ff769e083))
* Adjust ledger formatting ([61ca61](https://github.com/itinerare/Aldebaran/commit/61ca61f319263df639af1a4eb63ba976df2be279))
* Admin type index category filter doesn't work ([b56380](https://github.com/itinerare/Aldebaran/commit/b56380f120a3fbf630bca2ae2f038c79f004ffec))
* Admin views not disabled when comms disabled ([020c34](https://github.com/itinerare/Aldebaran/commit/020c34cedaf05158bd3929f398461cef3e91b952))
* Better checking on commission type info access ([9e0776](https://github.com/itinerare/Aldebaran/commit/9e0776d535afcc513a20a30835267c3502da966f))
* Bug setting category toggle ([8e75bb](https://github.com/itinerare/Aldebaran/commit/8e75bbbf9e701f841e0f7ce73de3a20c96d610a8))
* Cannot clear payment ([ad4e15](https://github.com/itinerare/Aldebaran/commit/ad4e150e3fe54d3aa8e2e02b39784707874f71e6))
* Can view new commission page when class is disabled ([382b2a](https://github.com/itinerare/Aldebaran/commit/382b2a9bb098a0289e78d2d73f366d2055e2230b))
* Class not validated properly when creating/editing category ([86f602](https://github.com/itinerare/Aldebaran/commit/86f6020a665bb418d83443549efe60c44cb9a159))
* Comm creation does not check class active status ([6dde70](https://github.com/itinerare/Aldebaran/commit/6dde7084705260892c9163f9cadfebef3d2fda67))
* Comments not applied to comm when banning commissioner ([7af735](https://github.com/itinerare/Aldebaran/commit/7af735c9fdbf818e29a23e4589738bd653a63f5c))
* Commission creation does not check slot status ([19f160](https://github.com/itinerare/Aldebaran/commit/19f160150ea617485462d4a21f9534e329ea5a5d))
* Deleting class doesn't clean up pages, settings ([301416](https://github.com/itinerare/Aldebaran/commit/301416922d7e017e3a0a11d6ffe2180be358b8c4))
* Error deleting commission types ([7d582e](https://github.com/itinerare/Aldebaran/commit/7d582e9979bfd25df01e28296caa741f89d97678))
* Error including empty class/category field data ([abe706](https://github.com/itinerare/Aldebaran/commit/abe706b14e4991102db7b36b1f3458bc83f7e4b1))
* Error saving class data ([e2c421](https://github.com/itinerare/Aldebaran/commit/e2c4217368890bb8ed82f7763f7b86ee8201935d))
* Error setting include category on types ([73d067](https://github.com/itinerare/Aldebaran/commit/73d0672f16572c946bb5810c4913791fc19f19f9))
* Error viewing commissions when fields have been added ([ee8070](https://github.com/itinerare/Aldebaran/commit/ee807077aaaf27e87c744d7d7c6414f6e10bd5ac))
* Error viewing comms w/o cost data ([347a89](https://github.com/itinerare/Aldebaran/commit/347a8912f6efa319d8f90b72bca9f2e80903bf5b))
* Error viewing ledger ([fd4ec5](https://github.com/itinerare/Aldebaran/commit/fd4ec55f14d8394ad923cefeafca8d59d639e839))
* Error with cost data migrate ([566222](https://github.com/itinerare/Aldebaran/commit/566222cef3cd11b3576a0c6ed5312f484970aa08))
* Inactive comm classes not visible to user ([5e9dd4](https://github.com/itinerare/Aldebaran/commit/5e9dd4a585d6526bd4783ef79be68f295b7db634))
* Input group styling ([8e2a8b](https://github.com/itinerare/Aldebaran/commit/8e2a8b9d7f4b4fdb153a9072e71bf0e07f16b053))
* Issue setting include class/category for types ([c68d1c](https://github.com/itinerare/Aldebaran/commit/c68d1c0b7455c8d350cfbe4c32ffabfe87226a46))
* Ledger lists unpaid payments from cancelled commissions ([0336e3](https://github.com/itinerare/Aldebaran/commit/0336e392461d6a894646bfc8d1d013a1e34eecb1))
* Ledger not subject to commission disable ([4e95ed](https://github.com/itinerare/Aldebaran/commit/4e95ed44accc1e7c4a2e6cbce8afbb753d0c8090))
* Ledger title typo ([0a60f2](https://github.com/itinerare/Aldebaran/commit/0a60f2cefcfeef7b016eec440a0812e8eab525bc))
* Make existing comms viewable regardless of comms being enabled ([7d0dbf](https://github.com/itinerare/Aldebaran/commit/7d0dbf08b1984fc859f34d9cf2cbf7fb3559a4cf))
* Minor payment status verbiage ([8a8824](https://github.com/itinerare/Aldebaran/commit/8a88242943e26d0f9b7a9a15e549616c259a4da4))
* Overall class slots not fetched correctly ([a5309c](https://github.com/itinerare/Aldebaran/commit/a5309ca998fd02350c4b52059f2100965583fe8e))
* Payment note display nitpicking ([c1c220](https://github.com/itinerare/Aldebaran/commit/c1c220d6044e99bdcd63a3f3872525b336cd798d))
* Properly hide a tip of 0 in ledger ([d74905](https://github.com/itinerare/Aldebaran/commit/d74905e7bdbc095596a4a8f175b1d62088874c88))
* Remove redundant type fillable item ([d8dff2](https://github.com/itinerare/Aldebaran/commit/d8dff2739a3f2b5066323293d25d54e484790cc8))
* Type gallery visible when class is inactive ([15d99e](https://github.com/itinerare/Aldebaran/commit/15d99e1a21af8f3efc9c5bfc20b02edab6f0670f))

##### Comms

* Additional information ommitted ([1caf90](https://github.com/itinerare/Aldebaran/commit/1caf90e91a7b278bf912aa9ec181d4bda3534112))
* Change artist comments to just comments ([b3d748](https://github.com/itinerare/Aldebaran/commit/b3d7482271b1d7ea7523d7df05eb1db27df6655f))
* Change comm key to commission_key ([42c94e](https://github.com/itinerare/Aldebaran/commit/42c94ec7769e6ae6d015de4dfa8d6174868bf567))
* Commission request form not generic ([4c00f8](https://github.com/itinerare/Aldebaran/commit/4c00f89b11a016075c60af5717c5d72a5f46a065))
* Cost does not support decimals ([6083eb](https://github.com/itinerare/Aldebaran/commit/6083eb5a1c4b2ac6104a08308f4f0344b381e026))
* Entering tip overwrites existing data ([a5dcb1](https://github.com/itinerare/Aldebaran/commit/a5dcb1913ad485d85c3281a70be23feac87a598a))
* Error creating commission ([5b7d41](https://github.com/itinerare/Aldebaran/commit/5b7d41e31b88eff2aec0a1556d052468e66217d5))
* Error creating comm type ([c9052d](https://github.com/itinerare/Aldebaran/commit/c9052d6b0aea05f840621200a74471ca0c268fe2))
* Error generating keyed URLs ([80a7d9](https://github.com/itinerare/Aldebaran/commit/80a7d90d5730e72210a32bb338164309a524d3ed))
* Error removing all pieces from comm ([52a81a](https://github.com/itinerare/Aldebaran/commit/52a81a3fce73bb1914175829cf1fbe8b940675f3))
* Errors creating comm admin-side ([96bb48](https://github.com/itinerare/Aldebaran/commit/96bb4836f1c39c054ef13d1d7e7d4efafa26e174))
* Error viewing comm w piece w no images ([ab25dd](https://github.com/itinerare/Aldebaran/commit/ab25ddb825da0d80febfd6b7f797888fd7de496c))
* Fix comm info display issues ([876fc9](https://github.com/itinerare/Aldebaran/commit/876fc95177f97eb381886df25b184c3ef3722490))
* Sort defaults new-old, not old-new ([bfb757](https://github.com/itinerare/Aldebaran/commit/bfb757f056c22a08c6f9847e412ab64d585f945c))
* Update options shown before accepting ([ab8849](https://github.com/itinerare/Aldebaran/commit/ab8849b9f04ff1462623ddefef1e3c577ea0b4a8))

##### Config

* Update image files ([345a14](https://github.com/itinerare/Aldebaran/commit/345a14334c18bdc5a1250c572d4fba048355898e))

##### Gallery

* Error viewing gallery when site isn't fully set up ([0acb28](https://github.com/itinerare/Aldebaran/commit/0acb284675c0efe94460b3af7107b9979b78c115))
* Issue w piece slugs w special chars ([2d6835](https://github.com/itinerare/Aldebaran/commit/2d6835f3d160e196a054dc88af9155f6be7194ab))

##### Pieces

* Add extra safeguard to text watermark ([7381d8](https://github.com/itinerare/Aldebaran/commit/7381d8e32f5f7cbe0c26f940446b414fda720cf8))
* Cannot remove set tags, programs; fixes #146 ([393b2a](https://github.com/itinerare/Aldebaran/commit/393b2aa6cd585bb49a05679fab7aa1be7625cc2b))
* Issue applying text watermark ([46bfa6](https://github.com/itinerare/Aldebaran/commit/46bfa659bbd56b38e04220a15b2c18a3c6279070))

##### Programs

* Adjust image handling to support tests ([d30cb0](https://github.com/itinerare/Aldebaran/commit/d30cb0f03558e69df480a06fd7108aa96ed4076e))
* Error adding program without icon ([1bf9ba](https://github.com/itinerare/Aldebaran/commit/1bf9baf82fdc584688a7cc0813d1e5fea20bcf8a))
* Errors editing with icon ([6b2ab6](https://github.com/itinerare/Aldebaran/commit/6b2ab6713388299d187ddb99b67d9ff4dc795a46))

##### Tests

* Comm type test setup error ([83466e](https://github.com/itinerare/Aldebaran/commit/83466e289780154b6856127e33b5dbba093c6f8a))
* Piece timestamp test error ([282488](https://github.com/itinerare/Aldebaran/commit/282488bf3c9245aac5150413aec008bec07177a0))
* Resolve piece image get edit test issue ([7b52c0](https://github.com/itinerare/Aldebaran/commit/7b52c0c4365f33f385e8dd7c03adbfeded42cb1e))
* Rogue " in comm class factory test data ([ad3acd](https://github.com/itinerare/Aldebaran/commit/ad3acd5cd3b611762f4955133494779ad42c342d))
* Skip piece image get edit due to cast incompat ([7390c2](https://github.com/itinerare/Aldebaran/commit/7390c20545416a18def472aaf011284605ed738f))
* Update comm tests for new setting ([3f9c91](https://github.com/itinerare/Aldebaran/commit/3f9c91388612f3f03609f06bab21a38ef80abc78))
* Update site settings test ([2d040b](https://github.com/itinerare/Aldebaran/commit/2d040b7660860fca1e0747b055caf0b8832fd502))


---

