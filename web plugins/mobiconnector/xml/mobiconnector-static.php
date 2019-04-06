<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.	
}
function bamobile_mobiconnector_get_static(){
    $statics = array(
        array(
            'label' => 'Title About us',
            'type' => 'text',
            'id' => 'mobiconnector-privacy-about-us',
            'className' => 'mobiconnector mobiconnector-about-us-title',
            'name' => 'mobiconnector-privacy-about-us',
            'placeholder' => 'Mobiconnector About us',
            'defaultValue' => 'About Us',
            'notice' => ''
        ),
        array(
            'label' => 'Description About us',
            'type' => 'editor',
            'id' => 'mobiconnector-description-about-us',
            'className' => 'mordernshop mobiconnector-editor mobiconnector-description-about-us',
            'name' => 'mobiconnector-description-about-us',
            'placeholder' => 'Mobiconnector Description About us',
            'defaultValue' => 'We would like to tell you a little bit about what makes our store special and why you should shop with us. For starters, we have a <strong>20,000 square foot store</strong> that stocks over 20,000 dresses. That’s right, you will not find anyone else out there that carries that much stock, especially an online retailer. We register all the dresses sold at our store and maintain our motto of not selling the same dress in the same color to the same event.</p>
            <p>We have nearly every prom dress in every size and color from every prominent&nbsp;dress designer. This is extremely important in the prom industry because it has such a short selling season. If you are in a pinch in March or April looking for the hottest selling style, We will have that gown for you because we order a huge quantity to begin with. No one wants to take a chance on ordering a dress and waiting 10 weeks to receive it! Most girls want to try their dress on and be able to take it home that same day to show off to their family and friends. At our store, that is exactly what you can do!</p>
            <p>We is unique in that we dedicate 90% of our floor space to prom dresses. Many online stores only stock one size samples and do not even have a brick and mortar, not us. We like to think of ourselves as a tremendous retail store that warehouses all styles, sizes, and colors. Whatever your event may be: Quinceneara, Sweet Sixteen, Pageant, Prom, Homecoming, Engagement, Wedding, and any other event, We will have the dress of your dreams!</p>
            <p>While shopping for your perfect dress, our store’s staff is fully trained to provide the best customer service imaginable. Our staff is fluent in Polish and Spanish and they will pamper you like a princess and make you look perfect from head to toe. At our store, the staff understands what styles are the most figure flattering for your body. We carry gorgeous gowns in every size imaginable, from sizes 0-30 IN STOCK! Whether you are searching for a curve hugging mermaid dress, an elegant A-line style, or a Cinderella style ball gown, we’ll find the perfect dress for you! We carry stunning shoes and every accessory imaginable (earrings, necklace set’s, bracelets, tiaras, headbands, and much more!).</p>
            <p>We also provide on site alterations. The same day you purchase your dress, you can get pinned for alterations and not have to worry about finding a seamstress that you do not know and trust.</p>
            <p>We knows how important prom night is for you and that is why we strive to maintain our motto of never selling the same dress in the same color to the same event.',
            'notice' => ''
        ),
        array(
            'label' => 'Title Terms of use',
            'type' => 'text',
            'id' => 'mobiconnector-terms-ofuser-title',
            'className' => 'mordernshop mobiconnector-terms-ofuser-title',
            'name' => 'mobiconnector-terms-ofuser-title',
            'placeholder' => 'Mobiconnector Terms of use',
            'defaultValue' => 'Terms and Conditions of Use',
            'notice' => ''
        ),
        array(
            'label' => 'Description Terms of use',
            'type' => 'editor',
            'id' => 'mobiconnector-description-term-ofuse',
            'className' => 'mordernshop mobiconnector-editor mobiconnector-description-term-ofuse',
            'name' => 'mobiconnector-description-term-ofuse',
            'placeholder' => 'Mobiconnector Description Terms of use',
            'defaultValue' => '<h2>1. Terms</h2>
            <p>By accessing this web site, you are agreeing to be bound by these web site Terms and Conditions of Use, all applicable laws and regulations, and agree that you are responsible for compliance with any applicable local laws. If you do not agree with any of these terms, you are prohibited from using or accessing this site. The materials contained in this web site are protected by applicable copyright and trade mark law.</p>
            <h2>2. Use License</h2>
            <p>Permission is granted to temporarily download one copy of the materials (information or software) on our web site for personal, non-commercial transitory viewing only. This is the grant of a license, not a transfer of title, and under this license you may not:</p>
            <ul>
            <li>modify or copy the materials;</li>
            <li>use the materials for any commercial purpose, or for any public display (commercial or non-commercial);</li>
            <li>attempt to decompile or reverse engineer any software contained on Feedy’s web site;</li>
            <li>remove any copyright or other proprietary notations from the materials; or</li>
            <li>transfer the materials to another person or “mirror” the materials on any other server.</li>
            </ul>
            <p>This license shall automatically terminate if you violate any of these restrictions and may be terminated by us at any time. Upon terminating your viewing of these materials or upon the termination of this license, you must destroy any downloaded materials in your possession whether in electronic or printed format.</p>
            <h2>3. Disclaimer</h2>
            <p>The materials on our web site are provided “as is”. We makes no warranties, expressed or implied, and hereby disclaims and negates all other warranties, including without limitation, implied warranties or conditions of merchantability, fitness for a particular purpose, or non-infringement of intellectual property or other violation of rights. Further, We does not warrant or make any representations concerning the accuracy, likely results, or reliability of the use of the materials on its Internet web site or otherwise relating to such materials or on any sites linked to this site.</p>
            <h2>4. Limitations</h2>
            <p>In no event shall We or our suppliers be liable for any damages (including, without limitation, damages for loss of data or profit, or due to business interruption,) arising out of the use or inability to use the materials on our Internet site, even if we or a our authorized representative has been notified orally or in writing of the possibility of such damage. Because some jurisdictions do not allow limitations on implied warranties, or limitations of liability for consequential or incidental damages, these limitations may not apply to you.</p>
            <h2>5. Revisions and Errata</h2>
            <p>The materials appearing on our web site could include technical, typographical, or photographic errors. We does not warrant that any of the materials on its web site are accurate, complete, or current. We may make changes to the materials contained on its web site at any time without notice. We does not, however, make any commitment to update the materials.</p>
            <h2>6. Links</h2>
            <p>We has not reviewed all of the sites linked to its Internet web site and is not responsible for the contents of any such linked site. The inclusion of any link does not imply endorsement by us of the site. Use of any such linked web site is at the user’s own risk.</p>
            <h2>7. Site Terms of Use Modifications</h2>
            <p>We may revise these terms of use for its web site at any time without notice. By using this web site you are agreeing to be bound by the then current version of these Terms and Conditions of Use.</p>
            <h2>8. Governing Law</h2>
            <p>Any claim relating to our web site shall be governed by the laws of the Vietnamese without regard to its conflict of law provisions.</p>
            <p>General Terms and Conditions applicable to Use of a Web Site.</p>',
            'notice' => ''
        ),
        array(
            'label' => 'Title Privacy policy',
            'type' => 'text',
            'id' => 'mobiconnector-privacy-policy-title',
            'className' => 'mordernshop mobiconnector-privacy-policy-title',
            'name' => 'mobiconnector-privacy-policy-title',
            'placeholder' => 'Mobiconnector Privacy policy',
            'defaultValue' => 'Privacy Policy',
            'notice' => ''
        ),
        array(
            'label' => 'Description Privacy policy',
            'type' => 'editor',
            'id' => 'mobiconnector-description-privacy-policy',
            'className' => 'mordernshop mobiconnector-editor mobiconnector-description-privacy-policy',
            'name' => 'mobiconnector-description-privacy-policy',
            'placeholder' => 'Mobiconnector Description Privacy policy',
            'defaultValue' => '<p>We use a third-party web analytics service to gather and monitor behavioral information. You must read and agree to the Privacy Policy of the provider of the web analytics service we use on the Site.</p>
            <h2>How do we collect information on our Users?</h2>
            <p>The type of information is individually identifiable information (” We will not collect any Personal Information from you without your approval. Among other things, we will not collect any chat or other correspondence between a User and any third-party.</p>
            <p>Learn about the preferences of Users and general trends on our Site (e.g. understand which software is more popular than others).</p>
            <p>Administer the Site, help diagnose problems with our server, to gather broad demographic information.</p>
            <h2>Personal Information is collected in order to:</h2>
            <p>Provide you with certain information and services available only to registered Users.</p>
            <p>Identify you when conducting customer service operations (such as license purchase and generation, providing product feedback, etc.).</p>
            <p>Contact Us, and we will make reasonable efforts to delete any such information pursuant to any applicable privacy laws.</p>
            <h2>Cookies &amp; Local Storage</h2>
            <p>When you visit the Site, Company may use industry-wide technologies such as “Cookies” (or similar technologies), which stores certain information on your computer (” Google Ad And Content Network Privacy Policy</p>
            <h2>Security</h2>
            <p>We take a great care in maintaining the security of the Site and your information and in preventing unauthorized access to it through industry standard technologies and internal procedures. However, we do not guarantee that unauthorized access will never occur.</p>
            <p>Users who have registered to the Site agree to keep their password in strict confidence and not disclose such password to any third party.</p>
            <h2>Third Party Sites</h2>
            <p>While using the Site you may encounter links to third party websites. Please be advised that such third party websites are independent sites, and we assume no responsibility or liability whatsoever with regard to privacy matters or any other legal matter with respect to such sites. We encourage you to carefully read the privacy policies and the terms of use or service of such websites.</p>
            <h2>Changes to the Privacy Policy</h2>
            <p>The terms of this Privacy Policy will govern the use of the Site and any information collected therein. Company reserves the right to change this policy at any time, so please re-visit this page frequently. In case of any material change, we will post a clear notice on the Site.</p>
            <p>Changes to this Privacy Policy are effective as of the stated “Last Update” and your continued use of the Site on or after the Last Update date will constitute acceptance of, and agreement to be bound by, those changes.</p>
            <h2>Got any Questions Contact Us</h2>
            <p>Email: <a href="mailto:contact@taydomobile.com">contact@taydomobile.com</a></p>
            <p>Mobile Phone: +84-985-685-218</p>',
            'notice' => ''
        ),
    );
    return $statics;
}
?>