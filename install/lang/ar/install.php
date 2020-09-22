<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Automatically generated strings for Moodle installer
 *
 * Do not edit this file manually! It contains just a subset of strings
 * needed during the very first steps of installation. This file was
 * generated automatically by export-installer.php (which is part of AMOS
 * {@link http://docs.moodle.org/dev/Languages/AMOS}) using the
 * list of strings defined in /install/stringnames.txt.
 *
 * @package   installer
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['admindirname'] = 'مجلد الإدارة';
$string['availablelangs'] = 'حزم اللغات المتاحة';
$string['chooselanguagehead'] = 'إختر اللغة';
$string['chooselanguagesub'] = 'الرجاء حدد لغة للتثبيت. هذه اللغة ستستخدم أيضاً كاللغة الافتراضية للموقع، لكن يمكنك تغييرها لاحقا.';
$string['clialreadyconfigured'] = 'ملف التهيئة config.php موجود سلفاً. لطفاً، استعمل الرابط ذي المسار admin/cli/install_database.php لتنصيب مودل في هذا الموقع.';
$string['clialreadyinstalled'] = 'ملف التهيئة config.php موجود سلفاً. لطفاً، استعمل الرابط ذي المسار admin/cli/install_database.php لترقية مودل في هذا الموقع.';
$string['cliinstallheader'] = 'برنامج تنصيب مودل {$a} عبر سطر الأوامر النصية';
$string['databasehost'] = 'مستضيف قاعدة البيانات';
$string['databasename'] = 'اسم قاعدة البيانات';
$string['databasetypehead'] = 'إختر مشغل قاعدة البيانات';
$string['dataroot'] = 'مجلد البيانات';
$string['datarootpermission'] = 'صلاحيات مجلدات البيانات';
$string['dbprefix'] = 'مقدمة الجداول';
$string['dirroot'] = 'مجلد مودل';
$string['environmenthead'] = 'يتم فحص البيئة';
$string['environmentsub2'] = 'كل إصدار من مودل يتطلب وجود إصدار معين من PHP على الأقل مع عدد من امتداداته الإجبارية.
يجري فحص شامل لبيئة العمل قبل كل تنصيب وترقية. لطفاً، تواصل مع المشرف على المخدم إن لم تكن على دراية بكيفية تنصيب الإصدار الأحدث من PHP أو امتداداته.';
$string['errorsinenvironment'] = 'فشلت مهمة التحقق من بيئة التشغيل!';
$string['installation'] = 'التثبيت';
$string['langdownloaderror'] = 'مع الأسف، اللغة "{$a}" تعذر تنزيلها. عملية التنصيب ستستمر باللغة الإنجليزية.';
$string['memorylimithelp'] = '<p>إن حد استعمال الذاكرة لـ PHP في مخدمك حالياً هو {$a}.</p>

<p>هذا قد يسبب لمودل مشاكل في الذاكرة لاحقاً، خصوصاً
إن كانت لديك الكثير من الوحدات مُمَكَّنة و/أو الكثير من المستخدمين.</p>

<p>نوصي بضبط ذاكرة PHP على حد أعلى إن أمكن، مثلاً 40M.
   هناك عدة طرق للقيام بذلك والتي بإمكانك تجربتها:</p>
<ol>
<li>إن كنت تستطيع القيام بذلك، أعد تجميع PHP مع المفتاح <i>--enable-memory-limit</i>.
    هذا سيسمح لمودل بضبط حد الذاكرة بنفسه.</li>
<li>إن كنت تستطيع الوصول إلى ملف php.ini، يمكنك تغيير الخاصية <b>memory_limit</b>
    هناك إلى شيء آخر مثل 40M. إن لم يكن بإمكانك الوصول إليه
فقد تستطيع مطالبة المشرف بالقيام بذلك من جانبه.</li>
<li>في بعض مخدمات PHP يمكنك إنشاء ملف .htaccess في مجلد مودل
وجعله محتوياً على السطر الآتي:
    <blockquote><div>php_value memory_limit 40M</div></blockquote>
    <p>مع ذلك، في بعض المخدمات، هذا من شأنه منع <b>كل</b> صفحات PHP من العمل
    (ستشاهد أخطاءً عند النظر إلى الصفحات) مما يحتم عليك إزالة ملف .htaccess.</p></li>
</ol>';
$string['paths'] = 'المسارات';
$string['pathserrcreatedataroot'] = 'مجلد البيانات ({$a->dataroot}) لا يمكن إنشاؤه من قبل برنامج التنصيب.';
$string['pathshead'] = 'تأكيد المسارات';
$string['pathsrodataroot'] = 'مجلد البيانات الرئيسي غير قابل للكتابة.';
$string['pathsroparentdataroot'] = 'المجلد ذي الرتبة الأعلى ({$a->parent}) غير قابل للكتابة. مجلد البيانات ({$a->dataroot}) لا يمكن إنشاؤه من قِبَل برنامج التنصيب.';
$string['pathssubadmindir'] = 'القليل جداً من مستضيفي الويب يستعمل /admin بمثابة رابط للوصول إلى لوحة التحكم أو ما سواها. لسوء الحظ هذا يتعارض مع الموضع القياسي لصفحات إدارة مودل. يمكنك حل هذه المشكلة عبر إعادة تسمية مجلد الإدارة admin في نسختك من هذا التنصيب، لتضع هذا الاسم الجديد هنا. مثلاً: <em>moodleadmin</em>. هذا من شأنه إصلاح روابط الإدارة في مودل.';
$string['pathssubdataroot'] = '<p>المجلد الذي يخزن فيه مودل كل المحتوى من الملفات التي يرفعها المستخدمون.</p>
<p>هذا المجلد ينبغي أن يكون قابلاً للقراءة والكتابة من قبل مستخدم مخدم الويب (عادة هو \'www-data\'، \'nobody\'، أو \'apache\').</p>
<p>ينبغي أن لا يكون متاحاً للوصول المباشر عبر الويب.</p>
<p>إن كان المجلد غير موجود حالياً، فعملية التنصيب ستحاول إنشاءه.</p>';
$string['pathssubdirroot'] = '<p>المسار الكامل للمجلد الذي يحتوي على ترميز نظام مودل.</p>';
$string['pathssubwwwroot'] = '<p>العنوان الكامل حيث سيتم الوصول به إلى مودل، بعبارة أخرى، العنوان الذي على المستخدمين إدخاله في شريط العناوين لمستعرض الإنترنت للوصول إلى مودل.</p>
<p>ليس من الممكن الوصول إلى مودل عبر عناوين متعددة. إن كان موقعك قابلاً للوصول عبر عناوين متعددة، فعليك اختيار أسهلها واستعمل إعادة توجيه دائمية من باقي العناوين إلى هذا العنوان.</p>
<p>إن كان موقعك قابلاً للوصول من شبكة الإنترنت ومن شبكتك الداخلية، الإنترانت، استعمل العنوان الخارجي على الإنترنت هنا.</p>
<p>إذا كان العنوان الحالي خاطئاً، لطفاً، غيِّر الرابط في شريط العناوين لمتصفحك ثم أعد عملية التنصيب.</p>';
$string['pathsunsecuredataroot'] = 'موضع مجلد البيانات الرئيسي غير مُؤَمن';
$string['pathswrongadmindir'] = 'مجلد المشرف غير موجود';
$string['phpextension'] = 'إمتداد PHP {$a}';
$string['phpversion'] = 'إصدار PHP';
$string['phpversionhelp'] = '<p> يتطلب مودل على الاقل وجود PHP بالاصدار 5.6.5 أو 7.1 (الإصدار 7.0.x فيه بعض القيود عند التشغيل).</p>
<p> انت تستعمل حالياً الإصدار {$a}.</p>
<p> يجب عليك ترقية PHP أو الانتقال إلى مستضيف آخر لديه إصدار أحدث من PHP.</p>';
$string['welcomep10'] = '{$a->installername} ({$a->installerversion})';
$string['welcomep20'] = 'أنت تشاهد هذه الصفحة لأنك تمكنت بنجاح من تنصيب وإطلاق
حزمة <strong>{$a->packname} {$a->packversion}</strong> في حاسبتك. تهانينا!';
$string['welcomep30'] = 'الإطلاق العائد لـ <strong>{$a->installername}</strong> يتضمن
التطبيقات المعدة لإنشاء بيئة تسمح لـ <strong>مودل</strong> بالعمل، وهي:';
$string['welcomep40'] = 'الحزمة تتضمن أيضاً <strong>مودل {$a->moodlerelease} ({$a->moodleversion})</strong>.';
$string['welcomep50'] = 'استعمال كل التطبيقات في هذه الحزمة محكوم برخصها. حزمة التنصيب <strong>{$a->installername}</strong> الكاملة هي <a href="https://www.opensource.org/docs/definition_plain.html">مفتوحة المصدر</a> وموزعة بموجب الرخصة <a href="https://www.gnu.org/copyleft/gpl.html">GPL</a>.';
$string['welcomep60'] = 'الصفحات الآتية ستقودك عبر خطوات سهلة التتبع لتنصيب
وتهيئة <strong>مودل</strong> في حاسبتك. يمكنك قبول
الإعدادات الافتراضية، أو إختيارياً، تغييرها بما يتناسب مع احتياجاتك الخاصة.';
$string['welcomep70'] = 'أنقر زر "التالي" أدناه لمتابعة عملية تنصيب
<strong>مودل</strong>.';
$string['wwwroot'] = 'عنوان الويب';
