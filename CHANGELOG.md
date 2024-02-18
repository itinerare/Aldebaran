<!--- BEGIN HEADER -->
# Changelog

All notable changes to this project will be documented in this file.
<!--- END HEADER -->

## [3.11.1](https://github.com/itinerare/Aldebaran/compare/v3.11.0...v3.11.1) (2024-02-18)

### Bug Fixes


##### Commands

* Use Str helper in admin user setup command ([2c6276](https://github.com/itinerare/Aldebaran/commit/2c6276054d6a691804e0e4d41977208c6201188b))


---

## [3.11.0](https://github.com/itinerare/Aldebaran/compare/v3.10.3...v3.11.0) (2024-01-28)

### Features

* Update to Laravel 10 ([2f9650](https://github.com/itinerare/Aldebaran/commit/2f96506119c04c1682ee9c0620c079bdfa198436))

##### Pieces

* Add option to manually crop piece image thumbnails ([bc85ae](https://github.com/itinerare/Aldebaran/commit/bc85aed278be45e1c2abe43ceecbf1b038e6e832))

##### Tests

* Update data providers ([91c90c](https://github.com/itinerare/Aldebaran/commit/91c90c92109387c05f107acfaf5b4efdcbd73528))
* Update phpunit config schema ([4a2a5f](https://github.com/itinerare/Aldebaran/commit/4a2a5fcd86b5d6d4a551c362180c858e0f6507a5))

### Bug Fixes


##### Auth

* Add fortify back to service providers ([554c29](https://github.com/itinerare/Aldebaran/commit/554c2980c0112a69cfe6945f1458f30ccf37a98f))

##### Commands

* Make default image command create assets dir if not extant ([6f3ef2](https://github.com/itinerare/Aldebaran/commit/6f3ef226d145176a7a292f81fbbf4a37ade09af3))
* Update site images tests ([a9889d](https://github.com/itinerare/Aldebaran/commit/a9889dd303afdea57d54437d36f576295fdb64ec))

##### Commissions

* Only query commission queues on admin index if enabled ([f0f9d4](https://github.com/itinerare/Aldebaran/commit/f0f9d4dac340f84efe71876b172ac4d7538fb044))

##### Gallery

* Use Str helper for literature thumbs ([76980e](https://github.com/itinerare/Aldebaran/commit/76980eee437b1f424ab1869eb5124d23c8b00bb6))

##### Pieces

* Eager load programs on piece views ([1b2cb9](https://github.com/itinerare/Aldebaran/commit/1b2cb991ef433b5e6b2a11f940ec8c1f966dcf8d))

##### Routes

* Add honeypot middleware to use ([3a81f1](https://github.com/itinerare/Aldebaran/commit/3a81f139d2bcd0e34496f88149cd5e5a907580a6))
* Fix commission ToS/queue routes ([313d87](https://github.com/itinerare/Aldebaran/commit/313d8776e373bc82138113fe50c72f204eeba8f9))
* Prevent commission class page route from clobbering other class routes ([a59ca2](https://github.com/itinerare/Aldebaran/commit/a59ca22286c9db6c6fe4da9fe41e67d815733a61))

##### Tests

* Add missing parent::tearDown() ([487023](https://github.com/itinerare/Aldebaran/commit/48702381e08fce2f04f6e4258b5366adbc0f52a4))
* Make invalid cases more consistent ([1aabc8](https://github.com/itinerare/Aldebaran/commit/1aabc87970f73594affd02f32e1d7f8bad4cb081))
* Update command reference in feed view tests ([330fe7](https://github.com/itinerare/Aldebaran/commit/330fe7935849bb029717a50e40d07cc2c2754802))
* Update command reference in gallery view tests ([8bd31d](https://github.com/itinerare/Aldebaran/commit/8bd31d056dc2594dd0e7384a889dd4ad22742516))
* Update command reference in page view tests ([6591d8](https://github.com/itinerare/Aldebaran/commit/6591d86fca70bf356c0e5fa71ebfaceb96e1ea34))
* Update command references in admin tests ([fef584](https://github.com/itinerare/Aldebaran/commit/fef584a0c75255fceb08068629f16575b83b294c))
* Update command references in commission/quote form tests ([846c2c](https://github.com/itinerare/Aldebaran/commit/846c2ce12b8bf8e2d5de811e48102c2585be42a8))
* Update program test image file references ([da70ff](https://github.com/itinerare/Aldebaran/commit/da70ff86eab74e0fa38bf558e9b7c187cc87c3e7))


---

## [3.10.3](https://github.com/itinerare/Aldebaran/compare/v3.10.2...v3.10.3) (2023-10-15)

### Bug Fixes


##### Commissions

* Account for PayPal sandbox certs ([341f93](https://github.com/itinerare/Aldebaran/commit/341f930bf144f2f9ffa82fe9fd7506ea2da9eb20))
* Attempt to defer PayPal webhook processing if payment is pending ([8ad040](https://github.com/itinerare/Aldebaran/commit/8ad0407296d198453096aeeb8759fddd434f1bd0))
* Switch PayPal webhook signature verification method ([ce9713](https://github.com/itinerare/Aldebaran/commit/ce97135a9d583022a420c1e1af51499809dfa206))


---

## [3.10.2](https://github.com/itinerare/Aldebaran/compare/v3.10.1...v3.10.2) (2023-08-13)

### Bug Fixes


##### Pieces

* Only show pieces in gallery with visible images/literatures for users ([3ace1b](https://github.com/itinerare/Aldebaran/commit/3ace1b67f71d01b3a3bb4d8e3c769886f4e64490))


---

## [3.10.1](https://github.com/itinerare/Aldebaran/compare/v3.10.0...v3.10.1) (2023-04-23)

### Bug Fixes

* Route form submissions through honeypot middleware ([fe0399](https://github.com/itinerare/Aldebaran/commit/fe03990cadd7bafc4ed216e95d150463714f1245))

##### Commissions

* Clarify global slots message ([70343e](https://github.com/itinerare/Aldebaran/commit/70343e2069b05600810747848b2fd5ae3af88304))


---

## [3.10.0](https://github.com/itinerare/Aldebaran/compare/v3.9.0...v3.10.0) (2023-04-16)

### Features


##### Commands

* Improve update command ([5528fa](https://github.com/itinerare/Aldebaran/commit/5528fa5aa84b47893910ad8f38c789213b0551c4))

##### Commissions

* Add invoice paid notif email ([2887d7](https://github.com/itinerare/Aldebaran/commit/2887d7b6d11813604610dbc639325d9e0bb40405))
* Include category name with type name in admin new quote type selection ([e097be](https://github.com/itinerare/Aldebaran/commit/e097be6cadcf526c54f6c0ead5d405790cfce727))

### Bug Fixes

* Better check when updating site settings ([c2e7c1](https://github.com/itinerare/Aldebaran/commit/c2e7c12a7223801096c433d3365b600da78c2c47))
* Site settings error banner incorrectly coloured ([365d7d](https://github.com/itinerare/Aldebaran/commit/365d7d6623857fc9e56f9053f2658d2639087e8d))

##### Commissions

* Better checking for presence of parent when retrieving invoice data ([4f2ebd](https://github.com/itinerare/Aldebaran/commit/4f2ebd7004d5b1f976ce312f8943f9661783bc59))
* Fix setting overall class slots ([0d6940](https://github.com/itinerare/Aldebaran/commit/0d69406e0335d522e5bdbe599f833d7940ced202))
* Only allow setting invoice information for extant categories, types ([2aadbd](https://github.com/itinerare/Aldebaran/commit/2aadbd170929a9e876aa21442f3379caeeeaec1b))
* Remove unnecessary honeypot fields from admin new quote/comm forms ([1640e3](https://github.com/itinerare/Aldebaran/commit/1640e3f0683731497381151a358f67eb055264b9))
* Update contact info form tooltip setting name ([f59172](https://github.com/itinerare/Aldebaran/commit/f5917216c6b0bb836f7f2f32e35f743c5d332237))
* Update references to class slot setting ([72bd38](https://github.com/itinerare/Aldebaran/commit/72bd386989affa88fe0387b9f61d4f18ddf0b291))


---

## [3.9.0](https://github.com/itinerare/Aldebaran/compare/v3.8.0...v3.9.0) (2023-04-09)

### Features


##### Commissions

* Add ability to provide a quote when requesting a commission ([ce4d97](https://github.com/itinerare/Aldebaran/commit/ce4d97777c5700e54a192f85c5ca09d1aca4366f))
* Add commission/quote notification mails ([c219dd](https://github.com/itinerare/Aldebaran/commit/c219dd4f68f11da895cd9618115596b8cd615406))
* Add public quote request form, view ([a8e8e6](https://github.com/itinerare/Aldebaran/commit/a8e8e6430bfacfc3f7b5499ce5f26e2e81be7b1f))
* Add quotes, admin queue, and handling ([781b75](https://github.com/itinerare/Aldebaran/commit/781b75fe2034f7cf7273b0c3d0f48be556b0b327))
* Add receive notifications to commissioners ([63575d](https://github.com/itinerare/Aldebaran/commit/63575d6fc00293f18cf17a0d11a50b0abca22d7f))
* Implement optional comm/quote notif mails ([ea43c9](https://github.com/itinerare/Aldebaran/commit/ea43c93a2c1b27ecda5ab74fc4ac5fa5cc2ef4c9))

##### Tests

* Add admin quote tests ([88f3c7](https://github.com/itinerare/Aldebaran/commit/88f3c7442ecda4e2e36179e3ae1b0a5ee4d0ce1d))
* Add check for comm to quote decline tests ([295ff3](https://github.com/itinerare/Aldebaran/commit/295ff359c518257d991166592d014c984a02979f))
* Add new quote to site page edit tests ([4c8b8c](https://github.com/itinerare/Aldebaran/commit/4c8b8ce6b96c835749d265f64eb6a8ab567e764f))
* Add post commission form with quote tests ([fc79bd](https://github.com/itinerare/Aldebaran/commit/fc79bd29e64e0d313a87638d58942e23465c31fb))
* Add quote form tests ([9660a3](https://github.com/itinerare/Aldebaran/commit/9660a3cdc232f17bd7ae83555d336f38983a8667))
* Add quote request to email contents tests ([407d72](https://github.com/itinerare/Aldebaran/commit/407d729678c2c02f83ae364ad786e937b74ab1ca))
* Add quote to commission view tests ([dedf0d](https://github.com/itinerare/Aldebaran/commit/dedf0d559da6f007ee66b80b4e144078beda1997))
* Add receive notifications to new commission, quote tests ([041486](https://github.com/itinerare/Aldebaran/commit/041486b16eb529bdd72f2a1d96f4c2bd79947810))
* Update admin queue tests for quote queues ([bddacf](https://github.com/itinerare/Aldebaran/commit/bddacfb6ad5bd6e6759554bcffb145dc7c401e23))
* Update commission notif email tests for new mails ([ffd458](https://github.com/itinerare/Aldebaran/commit/ffd4587c53e3dc921695b9d85cdcb108901abad2))
* Update comm/quote tests for notification mails ([048e8b](https://github.com/itinerare/Aldebaran/commit/048e8b9237de096c11d0e85e6f6f88ea3515d663))
* Update quote notif email tests for new mails ([08891b](https://github.com/itinerare/Aldebaran/commit/08891b1245fcef442c2c7f1c1e602ea965da13c4))

### Bug Fixes


##### Commissions

* Add line break to admin index after each comm class' queues ([bb7239](https://github.com/itinerare/Aldebaran/commit/bb7239fe77859c5086cbe101b2b7ce4a52d62196))
* Amount not displayed on quote ([f4bad9](https://github.com/itinerare/Aldebaran/commit/f4bad9f7feb3a18fd223853d4ab89ea0541ee63d))
* Autodecline quotes when banning a commissioner ([9312c0](https://github.com/itinerare/Aldebaran/commit/9312c01da6284806ff9852583d3ca0ffc60bbb54))
* Check that class is active when creating a new quote ([f9e552](https://github.com/itinerare/Aldebaran/commit/f9e5521493f4fe52f381043dba59fbca04685ecb))
* Disable new quote form when class is inactive ([918550](https://github.com/itinerare/Aldebaran/commit/91855041e4091a93d68e0f0dd01adf7f7ecf99dd))
* Fix filtering admin quote queue by type ([3ca910](https://github.com/itinerare/Aldebaran/commit/3ca910c3d6c811d7ffb01bc446f7b40aff0c6bcc))
* Fix quote request email template ([3d8a4b](https://github.com/itinerare/Aldebaran/commit/3d8a4bf6acd04ce12a01f1b47a16bd44de1e22e8))
* Improve check for quote commission link ([12fbe7](https://github.com/itinerare/Aldebaran/commit/12fbe7bb9491212b740c749fbb194539a38d9e20))
* Only display quote amount if accepted/complete ([34c8ec](https://github.com/itinerare/Aldebaran/commit/34c8ece4c97cb41e3024ae47ddfdee5921215064))
* Prevent declining a quote linked to an in-progress comm ([59abb7](https://github.com/itinerare/Aldebaran/commit/59abb7df36abd887d65130f780716384f4577cc0))
* Set all comm/quote mails to send after commit ([a37940](https://github.com/itinerare/Aldebaran/commit/a37940d75a98422937b2006fa5cf568977d2c6c5))
* Tidy up basic info formatting on commission page ([8fc447](https://github.com/itinerare/Aldebaran/commit/8fc4474a1aefd1579db216386a177ccaf0630965))


---

## [3.8.0](https://github.com/itinerare/Aldebaran/compare/v3.7.0...v3.8.0) (2023-03-19)

### Features


##### Commissions

* Add business name setting for PayPal integration ([21c114](https://github.com/itinerare/Aldebaran/commit/21c114625381c1646172d7e629c051ad628cfa5d))
* Add integration to send invoices via PayPal ([310da6](https://github.com/itinerare/Aldebaran/commit/310da639a32a64315429374fe1264077ac3dab4e))
* Add optional integration/setting Stripe product info per class/category/type/commission ([64b9b6](https://github.com/itinerare/Aldebaran/commit/64b9b6e2b38fd4a509c920bee716e5614d1eaa23))
* Add PayPal webhook endpoint and invoice data processing ([2d00b8](https://github.com/itinerare/Aldebaran/commit/2d00b817250b04d21ab28c9587af93c190c502c3))
* Add product category to invoice fields ([6e5480](https://github.com/itinerare/Aldebaran/commit/6e548062d5fa09653d1ec05312f9ae847e01b1d1))
* Better text on send invoice modal ([64dd0d](https://github.com/itinerare/Aldebaran/commit/64dd0d0fbc15d67f82ca8d95ec09f45421fb331f))
* Display payments on admin completed commission view ([4b87c3](https://github.com/itinerare/Aldebaran/commit/4b87c340e9bed69f7494bc9020e382ae6edff841))
* Implement semi-automated invoice handling via Stripe ([1d2572](https://github.com/itinerare/Aldebaran/commit/1d2572da9fb46c5180e6fbac45dce523701c46ff))
* Make invoice fields on individual commissions context-sensitive ([b4302b](https://github.com/itinerare/Aldebaran/commit/b4302b14e1eb4ca2c67acbfa7ef6540638a7950f))
* Make Stripe invoice due date configurable ([3e9bf8](https://github.com/itinerare/Aldebaran/commit/3e9bf85c115de63e327de69d35e51edb40ec1f0a))
* Reflect currency/symbol config values in views ([e8911f](https://github.com/itinerare/Aldebaran/commit/e8911f3f8d6a51370c0044e50428792046ad67da))
* Update fee retrieval for latest Stripe API ([9adf61](https://github.com/itinerare/Aldebaran/commit/9adf6111faa8aa45ed6daa5b2ec13bef1c11a01b))

### Bug Fixes


##### Commissions

* Also display payments on admin view when declined ([a47255](https://github.com/itinerare/Aldebaran/commit/a472559bbda194d4c815a3eb655641615e8a0ea9))
* Also hide tip label on new payment row if using integrations ([2be4c5](https://github.com/itinerare/Aldebaran/commit/2be4c53948909ec18b4b23d970e6b51c9bba6fd9))
* Carry over changes to new payment row ([d7260c](https://github.com/itinerare/Aldebaran/commit/d7260cbfc5d719f66c283cf571b425da6c7f32c5))
* Check if $parent is set for invoice info fields ([fe03db](https://github.com/itinerare/Aldebaran/commit/fe03db1a30758b54f682d766565bf0a78a91a80c))
* Display piece literatures on admin completed commission view ([41f768](https://github.com/itinerare/Aldebaran/commit/41f768801d65d4247fce769b33ff6c8f7959227b))
* Hide Stripe specific contents in invoice fields ([e24b58](https://github.com/itinerare/Aldebaran/commit/e24b58c55ad3f4217b98fcddcff56147914de8a2))
* Improve tip display/handling when using integrations ([b0d6da](https://github.com/itinerare/Aldebaran/commit/b0d6da272baabefabd56ba622575fe395ce2a358))
* Maintain invoice IDs even if integration is disabled ([d8ceb3](https://github.com/itinerare/Aldebaran/commit/d8ceb375b8666b784af4ba18efdd89344aaf8e98))
* Make payment cost non-editable once an invoice is sent ([8c1da7](https://github.com/itinerare/Aldebaran/commit/8c1da78834e4ad8987df81dc8d6953f5a21a4221))
* Only link to invoice if an ID is set ([93d300](https://github.com/itinerare/Aldebaran/commit/93d300eb8d4ac1c47414132eb1033d346f61dc55))
* Only show invoice information on Stripe commissions ([f3333a](https://github.com/itinerare/Aldebaran/commit/f3333aff1fd63c862a3acd8c0afde2a8a6299473))
* Pass on total with fees when updating commissions ([8e2e01](https://github.com/itinerare/Aldebaran/commit/8e2e010475ea6b263b33bb517f39cb99681f4fd5))
* Put back payment row label ([302d1f](https://github.com/itinerare/Aldebaran/commit/302d1fec503af5d8a3fadb7dd6826ef447ed1f31))
* Set default for payment tip field ([d40d6f](https://github.com/itinerare/Aldebaran/commit/d40d6fe0bc69c2b6a6c4944644ed35bd983c9f00))
* Set PayPal invoice category to services ([8db780](https://github.com/itinerare/Aldebaran/commit/8db780e8f1e84ad5b414740a946014996122736b))
* Set tip field properly when hidden ([168869](https://github.com/itinerare/Aldebaran/commit/168869fdf6271a284cba90e984747ad7da33746e))
* Show invoice fields when PayPal integrations are enabled ([c3a777](https://github.com/itinerare/Aldebaran/commit/c3a77734a3f3fcd3dda4e3c51e41bb4b34177411))
* Specify Stripe API version used ([56bba8](https://github.com/itinerare/Aldebaran/commit/56bba88343eb0855426928dcb4726e7d9f893296))
* Uncomment webhook unknown event logging ([81760c](https://github.com/itinerare/Aldebaran/commit/81760cbf747d6461b133f1e23ca3007c73a3155f))
* Update some missed currency symbols ([746c32](https://github.com/itinerare/Aldebaran/commit/746c32469f14cd6fc8ca1a7f0c68b05a2b7e2689))
* Update Stripe invoice creation method to account for new API version ([d2a486](https://github.com/itinerare/Aldebaran/commit/d2a48659c8c2b636af17237f6736ddb0865db3fd))


---

## [3.7.0](https://github.com/itinerare/Aldebaran/compare/v3.6.0...v3.7.0) (2023-03-12)

### Features


##### Commissions

* Add support for multiple/different payment processors ([411b80](https://github.com/itinerare/Aldebaran/commit/411b8018cb30243403a98541064bd4fe218c28d0))
* Make contact info field tooltip configurable via site settings ([990ddb](https://github.com/itinerare/Aldebaran/commit/990ddbacaed09899934c3ebb28d5fa6fc76e12e1))
* Sort unpaid payments in ledger under current month ([f32ae8](https://github.com/itinerare/Aldebaran/commit/f32ae8dc1b31acc357cfc2f47e7ab4c90d59c10f))
* Store total with fees for paid payments ([19bce7](https://github.com/itinerare/Aldebaran/commit/19bce7725f50b2b3dd8f3498c06aa69e7d702322))

##### Tests

* Add payment processor to new commission tests ([36d307](https://github.com/itinerare/Aldebaran/commit/36d3073a13098d62c660193a509dcb973e76de52))

### Bug Fixes


##### Commissions

* Also order payments initially by paid at in ledger ([a8fa40](https://github.com/itinerare/Aldebaran/commit/a8fa4095378d262cf34e4614f0d8f4d51efda605))
* Better month sorting in ledger ([60cd28](https://github.com/itinerare/Aldebaran/commit/60cd28937b31e4a81eb62cd21b6324cd0cb70ea7))
* Check if payment processor is enabled when creating commission ([43b145](https://github.com/itinerare/Aldebaran/commit/43b145c85212ed3c7ee6e1e1ab88e694de3b9345))
* Improved ledger sorting ([ac83ed](https://github.com/itinerare/Aldebaran/commit/ac83ed051a55ee78b06bc38c26f6f76327a0f1f4))
* Remove non-general instructions around contact info field ([397d04](https://github.com/itinerare/Aldebaran/commit/397d04e4bd6b942c5d1c7ef7c35cb518dc6e5fbd))
* Remove unnecessary reindex in ledger ([5e8150](https://github.com/itinerare/Aldebaran/commit/5e815074ffdf2aea37487742b4bbfc6952e1d500))

##### Tests

* Check payment processor in new commission tests ([00d282](https://github.com/itinerare/Aldebaran/commit/00d2820566e36572afad8aa3ddd69fecd4d31c91))


---

## [3.6.0](https://github.com/itinerare/Aldebaran/compare/v3.5.0...v3.6.0) (2023-03-05)

### Features

* Add admin edit button component ([209ae7](https://github.com/itinerare/Aldebaran/commit/209ae73db71f7f51cb16ee4015d6a8bfc994ec2f))
* Add admin edit buttons for a majority of editable objects to public-facing pages ([03f179](https://github.com/itinerare/Aldebaran/commit/03f179e57f4c610185a5a82d7fe9c29725b37fc2))
* Add optional styling override to admin edit button component ([904be6](https://github.com/itinerare/Aldebaran/commit/904be679212b0366a83696200de4e364127ba3ef))

##### Commissions

* Add valid scope to commissioners ([a93935](https://github.com/itinerare/Aldebaran/commit/a93935d28c78f6cadfd381e7a9f65769e0d68e49))
* Make breadcrumbs and prev/next buttons when viewing an example piece context-sensitive ([b818d8](https://github.com/itinerare/Aldebaran/commit/b818d8779640162abe6dabc9e5c9f04724b99e3a))
* Make selectable progress states configurable ([5b9c11](https://github.com/itinerare/Aldebaran/commit/5b9c11ce89dce65e5ceef66d8b3de843db115b4c))
* Tidy access checking for type galleries, improve breadcrumbs ([b73b25](https://github.com/itinerare/Aldebaran/commit/b73b252bd647fb79a7a15cf02ce49f289cdd3b58))
* Update commission request notif email format to markdown template ([ab94f8](https://github.com/itinerare/Aldebaran/commit/ab94f87a465f027e220560c2d068a5ec3f16a7b5))

##### Email

* Add email_features config setting ([1c0274](https://github.com/itinerare/Aldebaran/commit/1c027423f9ca7becdc8a5e85a658e36bcc94b354))

##### Gallery

* Add aria labels to piece prev/next bttons ([d2e642](https://github.com/itinerare/Aldebaran/commit/d2e642f69dfc61211e6a4bdc33ba1df969cda101))
* Add optional previous/next buttons to pieces ([a13d21](https://github.com/itinerare/Aldebaran/commit/a13d21f3ecaad6e09cee778c5e75ee4d3adc345a))
* Improve origin detection when viewing a piece ([4b7bf2](https://github.com/itinerare/Aldebaran/commit/4b7bf211a50f7f5be76280192f8532582af416fc))

##### Mailing Lists

* Add subscriber count to mailing list index ([0b9c90](https://github.com/itinerare/Aldebaran/commit/0b9c90ed8b69cfa4d2f43521e879aa055a39f7ae))
* Add subscriber list to admin edit page, kicking/banning subscribers ([c91268](https://github.com/itinerare/Aldebaran/commit/c912688fb19ae3002a12b8c0b7d0ce3516d74f7a))
* Add subscription/unsubscription info to subscription page ([3cbcfc](https://github.com/itinerare/Aldebaran/commit/3cbcfcddabc3b37380808bae678e9a4040428f2e))
* Add toggleable list of mailing lists on index page ([0c9d97](https://github.com/itinerare/Aldebaran/commit/0c9d97e365e332ffa014dd5ba31c8c2028e488a3))
* Add unverified subscriber count to admin index ([212c1a](https://github.com/itinerare/Aldebaran/commit/212c1aeac3e46e62720fd2e5bc28f51aca386a88))
* Allow verified scope to return either verified or non-verified subscribers ([df13f9](https://github.com/itinerare/Aldebaran/commit/df13f9c75b211fba397f54991c5b442f26675df9))
* Basic entry creation/editing ([cb038b](https://github.com/itinerare/Aldebaran/commit/cb038b2a858a49351fb0e8b965918d79a577452d))
* Basic mailing list create/update/delete ([3dcc46](https://github.com/itinerare/Aldebaran/commit/3dcc469e7b720d9ce3fede1be5c86a1d984548ef))
* Basic subscription, verification ([26ad41](https://github.com/itinerare/Aldebaran/commit/26ad410eeec044f96d89b9b7c01573b879062f1e))
* Display entry ID on admin mailing list edit page ([5795be](https://github.com/itinerare/Aldebaran/commit/5795bebfcd9981c79bc5f44bb25504753ef6ec63))
* Display subscription page URL on mailing list edit page ([7dfc2b](https://github.com/itinerare/Aldebaran/commit/7dfc2b1c59e197502fc7935058f87f194308373b))
* Display when entries were sent on admin mailing list edit page ([b87f13](https://github.com/itinerare/Aldebaran/commit/b87f13d29384f6123498cc5055a83b31619911f9))
* Implement sending entries to subscribers via queue ([c01d9a](https://github.com/itinerare/Aldebaran/commit/c01d9ab85dac2037d2f103aca69c6329bae0eb40))
* Implement unsubscription flow for subscribers ([ad8209](https://github.com/itinerare/Aldebaran/commit/ad82093fa3a58e1782679fb02052e65fe2e5dcee))
* Include mailing list name in subscription page title ([790632](https://github.com/itinerare/Aldebaran/commit/790632f71215be14bcea1913bd6ff22d7f6cfa16))
* Make admin panel subscriber list collapsible, auto-collapse ([d3b3ee](https://github.com/itinerare/Aldebaran/commit/d3b3ee692119b9627212e2a37edfe16b4486d374))

##### Pieces

* Perform timestamp check in an attribute ([105034](https://github.com/itinerare/Aldebaran/commit/1050343b32fc2a4ba1bddba6ce74ad554cca8130))

##### Tests

* Add email notification test to commission creation ([ca4a8e](https://github.com/itinerare/Aldebaran/commit/ca4a8e2bc6479f3d253d59acf1bc4c9dc7c78c83))
* Add entry/subscriber checks to mailing list create/edit/delete tests ([67372b](https://github.com/itinerare/Aldebaran/commit/67372ba149733b2138f39473f71025c592babf63))
* Admin mailing list subscriber kick/ban tests ([896cff](https://github.com/itinerare/Aldebaran/commit/896cff9c003f051d47b77db440cdf6e070d57e69))
* Basic email contents tests ([5187c8](https://github.com/itinerare/Aldebaran/commit/5187c8af7f9061b2504e79b6614d27e8459ab363))
* Basic mailing list create/edit/delete tests ([dd0356](https://github.com/itinerare/Aldebaran/commit/dd0356150dadc53ded186b034954e4ec0b8ae87b))
* Mailing list entry tests ([24e491](https://github.com/itinerare/Aldebaran/commit/24e491d36f3e77cc936f932efb45fd88d4754005))
* Mailing list subscriber tests ([890649](https://github.com/itinerare/Aldebaran/commit/890649087e70ffccc0b8c5b50ce89adbe4ef8574))

### Bug Fixes


##### Changelog

* Correctly label deletion modal ([b7d318](https://github.com/itinerare/Aldebaran/commit/b7d318a70d5099b332fcd63f547d06132616cc98))

##### Commissions

* Change check for id when banning commissioner ([f5234e](https://github.com/itinerare/Aldebaran/commit/f5234ed3cc86e0f42973bf7ef1965fd3038db9ee))
* Eager load commissioner info on queue pages ([9bd06b](https://github.com/itinerare/Aldebaran/commit/9bd06b5fe00c5f74dfa25b5aa88e7844cf42cdd6))

##### Gallery

* Adjust prev/next button styling for visibility ([0f9eb9](https://github.com/itinerare/Aldebaran/commit/0f9eb922fdda39a7fdafd567920f9d54cad411cb))
* Correctly retrieve program name in alt text when viewing piece ([625123](https://github.com/itinerare/Aldebaran/commit/625123fc2a80c20b0c3f586350caefa7b7ebbd0c))
* Only show edit button on gallery if page exists ([1ce37a](https://github.com/itinerare/Aldebaran/commit/1ce37ab4b056f01cdc8db4b8e4856aeccc4f5819))
* Remove unnecessary break on piece page ([b545c7](https://github.com/itinerare/Aldebaran/commit/b545c745242f6e891bb85b3c9abf9739c33c5daf))

##### Mailing Lists

* Better UX around failed verification ([f21d2c](https://github.com/itinerare/Aldebaran/commit/f21d2c090feecd24dfd2553d824c993cc12c466c))
* Check that mailing list is open when creating subscription ([84df29](https://github.com/itinerare/Aldebaran/commit/84df29052aeb841da924c8365389bba302d67cf5))
* Check that subscription is not already verified when verifying ([673f6a](https://github.com/itinerare/Aldebaran/commit/673f6a4f18f11f62abc85a06c532b75296fa2fb4))
* Correct admin mailing list create/edit breadcrumbs ([177040](https://github.com/itinerare/Aldebaran/commit/17704064033594ad31afc3aa243135fa7712ecb9))
* Correctly set last entry set for subscribers ([6c5340](https://github.com/itinerare/Aldebaran/commit/6c534028801820e8cdd0622c27b96012a55f9487))
* Delete subscribers and entries when deleting mailing list ([2d06d4](https://github.com/itinerare/Aldebaran/commit/2d06d48203d5b1eed3cc372f1651bca1334d2d98))
* Display entry created at time on admin index if none have been sent ([a1b1ea](https://github.com/itinerare/Aldebaran/commit/a1b1ea6659ce78b5e4fb3a8eefc5a9424d0234f6))
* Enforce unique mailing list names ([8db3c5](https://github.com/itinerare/Aldebaran/commit/8db3c55fc428820f29e4b746df60e1f0f5bf2ada))
* Improve redirects on subscription verification/unsubscription ([937f83](https://github.com/itinerare/Aldebaran/commit/937f83a3b4c57b990d63be4b57f1d53d3134dce1))
* More thorough checking for email features being enabled ([d42f86](https://github.com/itinerare/Aldebaran/commit/d42f86e183c38acbf575c4c38f2e52f69650dd47))
* Move misplaced quotes in verification email template ([69c76b](https://github.com/itinerare/Aldebaran/commit/69c76b31083434443fd84dfd23b1bef5365d057f))
* Only specify mailing list when creating entry ([d52fa1](https://github.com/itinerare/Aldebaran/commit/d52fa1524ee752b3891688b662607fb20d1b1250))
* Properly display last entry on admin index ([399361](https://github.com/itinerare/Aldebaran/commit/399361c8090a159ad3d27dcec0f3a9dc38632ca6))
* Remove dysfunctional email check ([db58b4](https://github.com/itinerare/Aldebaran/commit/db58b4ac11e4fdb0c6bd4d578c1a575fc9171490))
* Slightly less redundant subscription page info ([447259](https://github.com/itinerare/Aldebaran/commit/447259076bf4736900ec462828360edbaf63d6a6))
* Subscription page visible when mail features are disabled ([51ef44](https://github.com/itinerare/Aldebaran/commit/51ef4432d510c7c930d4dea2480a9e2f43d824f3))
* Un-indent markdown mail ([d014bd](https://github.com/itinerare/Aldebaran/commit/d014bd34a598636f3a127f4348b0608c294fdead))

##### Pieces

* If main gallery is disabled, never consider pieces able to be shown in it ([7c475e](https://github.com/itinerare/Aldebaran/commit/7c475e17e6d68b181fa92dfc81c9499f0352aace))
* Refine inactive tag check re gallery inclusion ([9f2753](https://github.com/itinerare/Aldebaran/commit/9f27530ef37c6306d9099afb60d03bf4500688bb))

##### Tests

* Add text pages when performing gallery view tests ([6b36b4](https://github.com/itinerare/Aldebaran/commit/6b36b4a063de9b86bad249a2e5471114476109a7))
* Clean up changelog create test assertion ([45c763](https://github.com/itinerare/Aldebaran/commit/45c7635af37b7df679f0eb826dd69ccd4d9ddb28))
* Disable commission request notif email check ([2bbe54](https://github.com/itinerare/Aldebaran/commit/2bbe54d718a4d40c72c85fa9464fd8907c8b6ab3))


---

## [3.5.0](https://github.com/itinerare/Aldebaran/compare/v3.4.0...v3.5.0) (2023-02-18)

### Features

* Implement model correctness/performance checks ([365e4d](https://github.com/itinerare/Aldebaran/commit/365e4d9a805f59716688886891dc681cf8ce678f))

##### Pieces

* Show literatures count in admin index ([96ea73](https://github.com/itinerare/Aldebaran/commit/96ea7382ee2809549a4a411a2035cc779ab4777e))

### Bug Fixes


##### Commissions

* Better data filtering on class/category/type create/update ([2efd90](https://github.com/itinerare/Aldebaran/commit/2efd9063edabbf0f45c50410d7abe2bfeccec1c8))
* Better data filtering on commission update ([9686c5](https://github.com/itinerare/Aldebaran/commit/9686c570d529897934727e353d4a0d8457c2b8dd))
* Commission type not eager loaded in type index ([5b62a6](https://github.com/itinerare/Aldebaran/commit/5b62a69e4900de61080857e385ecba14f9879437))
* Correct piece name fetching in alt text on commission view ([e822c5](https://github.com/itinerare/Aldebaran/commit/e822c53d1a1b274398150b04046d0270c870e741))
* Do not always attempt to eager load commissioner on commission object ([8dab6c](https://github.com/itinerare/Aldebaran/commit/8dab6c9e3d86ccbd77f84ab2732f248b52327ab0))
* Eager load info in type admin panel ([3bc6bf](https://github.com/itinerare/Aldebaran/commit/3bc6bf5f80f2b68a197028498cd552983b930cda))
* Eager load information in category admin panel ([d48ed4](https://github.com/itinerare/Aldebaran/commit/d48ed4e2dc7e16d20be0a382f2e5a40a793b1161))
* Eager load ledger info ([b4bafe](https://github.com/itinerare/Aldebaran/commit/b4bafe4804fba261717fc36bc74003e31afe9c97))

##### Gallery

* Correctly hide pieces on hidden projects ([fb75c2](https://github.com/itinerare/Aldebaran/commit/fb75c2045f7b79b185e5d6afd864d9135537b189))
* Include pieces with no tags in gallery by default ([5108b9](https://github.com/itinerare/Aldebaran/commit/5108b9c1c7ce3f7a3d4b6667ec4c6074b94076fc))

##### Pieces

* Better data filtering on create/update ([cbe457](https://github.com/itinerare/Aldebaran/commit/cbe4574e7e30bdc8c8dc7554a78ca558abc39b59))
* Better data filtering on literature create/update ([d0f0ac](https://github.com/itinerare/Aldebaran/commit/d0f0aca25123d2adcce0438771c888c3bc62923f))
* Eager load info for feeds ([6c3950](https://github.com/itinerare/Aldebaran/commit/6c3950a9cd988d890a055c278fa8c0c9237efbd1))
* Eager load tag info on piece tags by default ([233861](https://github.com/itinerare/Aldebaran/commit/233861891ac64290a8d56efd6744ca1e90c37006))
* Properly hide hidden pieces ([8345d4](https://github.com/itinerare/Aldebaran/commit/8345d4856cef358e8ddadfa9737c7702a1543f52))

##### Programs

* Better data filtering on create/update ([15605b](https://github.com/itinerare/Aldebaran/commit/15605bfe15ada0f0347b026fa0e617ae203e32b2))

##### Tests

* Properly update show_examples on type ([c9367f](https://github.com/itinerare/Aldebaran/commit/c9367f8072a9ed779cb18419db93408464904024))


---

## [3.4.0](https://github.com/itinerare/Aldebaran/compare/v3.3.0...v3.4.0) (2023-01-15)

### Features


##### Commands

* Add option to update-images to just update DB ([173746](https://github.com/itinerare/Aldebaran/commit/1737466fc3c39cd9546de44bebeeb2e790ae4ab8))
* Add overall confirm to update images command for convenience ([69dc4f](https://github.com/itinerare/Aldebaran/commit/69dc4f6777d3701b818c615f604cf6c2a2cd25fc))
* Add progress bar(s) to update images ([97c62e](https://github.com/itinerare/Aldebaran/commit/97c62e14de7c7e42463f7c633fd3614202364bc3))

##### Pieces

* Add image format settings/switch image storage to WebP ([890306](https://github.com/itinerare/Aldebaran/commit/890306401cf2668ac9035b7ba8fdc1faf28df0b6))
* Adust image format settings ([bc6eb8](https://github.com/itinerare/Aldebaran/commit/bc6eb8e288184f21c75aa1fa4c777c2598b19b34))

##### Tests

* Add commission image view tests ([bf40bf](https://github.com/itinerare/Aldebaran/commit/bf40bf5cbdb488d98aeb1cdcd7c31cc8ff961f92))
* Add piece image view tests ([c18a34](https://github.com/itinerare/Aldebaran/commit/c18a34a8994583d3ec77d4dc18d14b85df4cc2c9))
* Update piece image tests ([8c2464](https://github.com/itinerare/Aldebaran/commit/8c246436107fa205fb3ac82a0979d28d53bfe5b4))

### Bug Fixes

* Error creating changelog entries ([06d52f](https://github.com/itinerare/Aldebaran/commit/06d52f49bd49de945f79ecf7f29b76ddde6b52a6))
* Error reading default images ([3390de](https://github.com/itinerare/Aldebaran/commit/3390de8de28f316afd43d948fb13b56b8ae7c01f))
* Meta tag formatting ([1ebb43](https://github.com/itinerare/Aldebaran/commit/1ebb4304b4d87ca2c29c54afcbc8ea7bc1914b8b))
* PNG file specified in CSS ([52c4f3](https://github.com/itinerare/Aldebaran/commit/52c4f3ffbf9d30f84e637952dd0ffeb61c4aa34a))

##### Commands

* Add mime check to update images command ([b6d9cb](https://github.com/itinerare/Aldebaran/commit/b6d9cb613298f0041177bc9dca43c3ef24cdb68d))
* Add newline after update images progress bar for tidiness ([68969a](https://github.com/itinerare/Aldebaran/commit/68969a0daa87fb333640e88d48d2a8947354ab1d))
* Allow update images to detect existing fullsize mime types ([7fc379](https://github.com/itinerare/Aldebaran/commit/7fc379e5c9390b8734c637b80b62e62dab8b0af2))
* Error in update images info text ([0fb778](https://github.com/itinerare/Aldebaran/commit/0fb7785589a6abc64de528923143d6297a13fbe8))

##### Pieces

* Adjust default image format settings for the moment ([0b6e49](https://github.com/itinerare/Aldebaran/commit/0b6e492a18634e0226bfbe808974c32dfdbebaca))
* Check for file existence when regenerating thumb/watermarked image ([4a7542](https://github.com/itinerare/Aldebaran/commit/4a754205e65c9450a369dc12ce9e618956d081b9))
* Error applying text watermarks ([4ab49a](https://github.com/itinerare/Aldebaran/commit/4ab49a79d69f19fdde6641392d98aeb648790460))
* Image extension not properly set per config on image creation ([4e222e](https://github.com/itinerare/Aldebaran/commit/4e222efdfd8c7f0334cfe0d2d27e808a50fe527d))

##### Tests

* Adjust site image tests ([3cab80](https://github.com/itinerare/Aldebaran/commit/3cab80c648e39469172c2d11db67c044a26cd58f))
* Update piece image tests ([bef295](https://github.com/itinerare/Aldebaran/commit/bef295fbaeb68f77ef652c181f31cf27d981566f))


---

## [3.3.0](https://github.com/itinerare/Aldebaran/compare/v3.2.1...v3.3.0) (2022-07-24)

### Features

* Add format to npm scripts ([3ef34c](https://github.com/itinerare/Aldebaran/commit/3ef34c103c0dc90f3827f9510b79771d234ea9e2))


---

## [3.2.1](https://github.com/itinerare/Aldebaran/compare/v3.2.0...v3.2.1) (2022-07-03)

### Bug Fixes


##### Css

* Update font references ([ee8873](https://github.com/itinerare/Aldebaran/commit/ee8873128be46751eb37cf9a42332b99e6a73420))


---

## [3.2.0](https://github.com/itinerare/Aldebaran/compare/v3.1.0...v3.2.0) (2022-05-29)
### Features

* Make captcha optional ([29b830](https://github.com/itinerare/Aldebaran/commit/29b830742f52a312519994599c165c45bdc06d9f))

##### Accessibility

* Add aria labels to ambiguous nav/footer links ([d00aff](https://github.com/itinerare/Aldebaran/commit/d00aff6a48e8281e9659005bf70dcf16603f0e1c))
* Add configurable alt text for piece images ([acbccf](https://github.com/itinerare/Aldebaran/commit/acbccf9a4703edd407483ec9bf2b81e7816b2708))
* Add generic alt text to images ([4c117f](https://github.com/itinerare/Aldebaran/commit/4c117f915dd866e49aa7f93affdb01cfd1ab0df5))
* Add hidden "skip to main content" link to nav ([63107d](https://github.com/itinerare/Aldebaran/commit/63107dda28c1af1db708439c53baf68ac10b73e9))
* Clearer label for sitename/home link ([243b3f](https://github.com/itinerare/Aldebaran/commit/243b3f65185243613d6308e424dc454ffc0b88d0))
* Clearer labeling for form fields around the site ([e1f61e](https://github.com/itinerare/Aldebaran/commit/e1f61e52091b2856791da46512dc061c874a9e94))
* Update site image/css forms with clearer labeling ([2af4a0](https://github.com/itinerare/Aldebaran/commit/2af4a0acf16e2cbb8bd7fe20e5dfa877d59f2c55))
* Update site settings forms with clearer labeling ([7bf214](https://github.com/itinerare/Aldebaran/commit/7bf214d14a165e5cbe3c8316c9142154d97339f0))

##### Commissions

* Add tooltip to manual comm creation button ([976315](https://github.com/itinerare/Aldebaran/commit/976315db4776bad303bcac9e51e7864ee706fcb0))
* Better support for literature attached to comms ([70cf7d](https://github.com/itinerare/Aldebaran/commit/70cf7d4124d5c73ad40021a8182fa9d3883bc5f4))

##### Tests

* Extend commission view tests for further literature support ([b0d9a2](https://github.com/itinerare/Aldebaran/commit/b0d9a2ee27469135d476960ac6760e77a0770c33))
* Extend piece view tests for alt text ([a789ff](https://github.com/itinerare/Aldebaran/commit/a789fff6a66ba149999caf673eb9e2b75329578b))
* Extend site settings view test to include comms enabled/disabled ([5ccd5f](https://github.com/itinerare/Aldebaran/commit/5ccd5fd85229597cace764993007f57f4e172ed0))

### Bug Fixes

* CSS sidebar background reference ([938f38](https://github.com/itinerare/Aldebaran/commit/938f38a0b223dd7f79edaaabb36922ac56f5ce00))

##### Commands

* Dummy commissioner setup not included in setup command ([a53e1c](https://github.com/itinerare/Aldebaran/commit/a53e1c712d24fd9cd5686ac9279bd4edfa3dccf0))

##### Users

* Move email uniqueness check to service to prevent unnecessary error ([ceb4cb](https://github.com/itinerare/Aldebaran/commit/ceb4cb47bbe69f2739a3f9606dc5c58051bcc002))


---

## [3.1.0](https://github.com/itinerare/Aldebaran/compare/v3.0.0...v3.1.0) (2022-05-22)
### Features


##### Pieces

* Add display of literatures ([bcb876](https://github.com/itinerare/Aldebaran/commit/bcb87670bad4f32e00342db95cb29c0d1092b9f8))
* Add support for creating and adding literatures ([b862b2](https://github.com/itinerare/Aldebaran/commit/b862b22c5e9bf61452ae63516766c8b6fdb92b32))

##### Tests

* Add piece image delete tests ([b94b5b](https://github.com/itinerare/Aldebaran/commit/b94b5b0ea9d663f2299e3cf4fddb8886832b0f75))
* Add piece literature create/edit/delete tests ([522da3](https://github.com/itinerare/Aldebaran/commit/522da35d056226ba3f108642c88ef0e909fbd26c))
* Extend gallery-type, piece view tests with literature support ([51f940](https://github.com/itinerare/Aldebaran/commit/51f940d951a62c3fb5c2f8ae527ed3b5ebef5afc))
* Extend piece image get create/edit tests ([816fda](https://github.com/itinerare/Aldebaran/commit/816fda72892e0440117e4f5bab7089635a82f146))

### Bug Fixes


##### Pieces

* Attempting to create an image/literature for an invalid piece does not 404 ([54c0f4](https://github.com/itinerare/Aldebaran/commit/54c0f47bbc9c377cd879d82a83f32b2c83372777))
* Missing checks for invalid image/literature when deleting ([2329c3](https://github.com/itinerare/Aldebaran/commit/2329c3a816e5f0d2589df485ec55158f736f1128))
* Piece lit thumbs not properly cleaned up on update ([3904ad](https://github.com/itinerare/Aldebaran/commit/3904adf9bb371a479cb879a70a014d8762b65d77))


---

## [3.0.0](https://github.com/itinerare/Aldebaran/compare/v2.1.1...v3.0.0) (2022-05-15)
### ⚠ BREAKING CHANGES

* Update to Laravel 9 ([f56125](https://github.com/itinerare/Aldebaran/commit/f561256258df0d94150b3dbbc725d3d5e93d91bd))
* Update to PHP 8 ([a9f1a4](https://github.com/itinerare/Aldebaran/commit/a9f1a4c525685e16364e8dcf6b9bd8a93db0eb77))

### Features


##### Backups

* Make backups optional ([d613b1](https://github.com/itinerare/Aldebaran/commit/d613b1133bfb15e9f701dc65f17f214b0ade9c02))

### Bug Fixes

* TrustProxies middleware error ([dd1b7f](https://github.com/itinerare/Aldebaran/commit/dd1b7f920f24aeded9a1c12353923bf9e4acebf1))

##### Backups

* Error with drobox driver ([bf1af6](https://github.com/itinerare/Aldebaran/commit/bf1af6178a76e4e6737e4fb2828b96ca666eba77))

##### Feeds

* Error generating feed items ([659723](https://github.com/itinerare/Aldebaran/commit/6597233e04292f54f1e13977c22d943ac6cd5ee8))

##### Tests

* Update assertDeleted to assertModelMissing ([fb5acc](https://github.com/itinerare/Aldebaran/commit/fb5acc541a1ff87a29e3a5af4556ebaec5c22862))


---

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

