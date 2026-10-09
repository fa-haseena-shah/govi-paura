
(function () {
  const DICT = {
    en: {
      // Brand / topbar
      notifications: 'Notifications',
      logout: 'Log out',
      signedInAs: 'Signed in as',

      // Farmer nav
      nav_dashboard: 'Dashboard',
      nav_listings: 'My Listings',
      nav_bids: 'Bids Received',
      nav_orders: 'Orders',
      nav_price_trends: 'Price Trends',
      nav_ratings: 'Ratings & Reviews',
      nav_notifications: 'Notifications',
      nav_helpdesk: 'Helpdesk',
      nav_profile: 'Profile',
      nav_account: 'Account',

      // Rider nav
      nav_available_deliveries: 'Available Deliveries',
      nav_assigned_deliveries: 'Assigned Deliveries',
      nav_delivery_history: 'Delivery History',
      nav_subscription: 'Subscription',

      // Buyer nav
      nav_browse_listings: 'Browse Produce',
      nav_my_bids: 'My Bids',
      nav_cart_checkout: 'Cart & Checkout',
      nav_my_orders: 'My Orders',

      // Admin nav
      nav_moderation: 'Moderation',
      nav_system: 'System',
      nav_verify_users: 'Verify Users',
      nav_manage_users: 'Manage Users',
      nav_manage_listings: 'Manage Listings',
      nav_manage_bids_orders: 'Bids & Orders',
      nav_manage_reviews: 'Reviews',
      nav_complaints: 'Complaints',
      nav_reports: 'Reports',
      nav_system_logs: 'System Logs',

      // Common buttons
      btn_save: 'Save Changes',
      btn_cancel: 'Cancel',
      btn_submit: 'Submit',
      btn_login: 'Log In',
      btn_create_account: 'Create Account',
      btn_search_ph: 'Search...',

      // Common badges / statuses
      status_active: 'Active',
      status_pending: 'Pending',
      status_completed: 'Completed',
      status_verified: 'Verified',
      status_suspended: 'Suspended',

      // Auth role tabs
      role_farmer: 'Farmer',
      role_buyer: 'Buyer',
      role_rider: 'Rider',
      role_admin: 'Admin',

      // Auth pages
      auth_login_quote: "Here's where you all sign in.",
      auth_login_quote_sub: "We'll take you straight to your dashboard once you're signed in.",
      auth_login_title: 'Welcome back',
      auth_identifier_label: 'Mobile number or email',
      auth_password_label: 'Password',
      auth_remember: 'Remember me',
      auth_no_account: 'New to Govi Paura?',
      auth_create_account_link: 'Create an account',
      auth_register_quote: '"Whichever role you play in the harvest journey, you start here."',
      auth_register_quote_sub: 'Farmers and riders are verified by NIC before they can trade or deliver. Buyer accounts are ready instantly.',
      auth_register_title: 'Create your account',
      auth_register_sub: "How would you like to use Govi Paura?",
      auth_role_label: 'I am a...',
      auth_preferred_language: 'Preferred language',
      language_english: 'English',
      language_sinhala: 'Sinhala',
      auth_full_name: 'Full name',
      auth_phone: 'Mobile number',
      auth_email: 'Email',
      auth_nic: 'NIC number',
      auth_farm_location: 'Farm location',
      auth_nic_upload: 'Upload NIC (front & back)',
      auth_upload_hint: 'Click to upload — JPG, PNG or PDF, max 5MB',
      auth_vehicle_type: 'Vehicle type',
      auth_vehicle_number: 'Vehicle registration no.',
      auth_vehicle_upload: 'Upload vehicle registration document',
      auth_business_name: 'Business name (optional)',
      auth_delivery_address: 'Default delivery address',
      auth_confirm_password: 'Confirm password',
      auth_terms: 'I agree to the Govi Paura Terms of Service and Privacy Policy.',
      auth_have_account: 'Already have an account?',

      // Verification status
      verification_title: 'Verification submitted',
      verification_subtitle: 'Your application is now awaiting admin review.',
      verification_message: 'Please allow up to 24 hours for an admin to review your submission.',
      verification_status: 'Status',
      verification_pending: 'Pending verification',
      verification_return: 'Return to login',

      // Landing page — nav
      lp_nav_how: 'How It Works',
      lp_nav_portals: 'Portals',
      lp_nav_features: 'Features',
      lp_get_started: 'Get Started',

      // Landing page — hero
      lp_eyebrow: 'Web-Based B2B Farm-to-Retail Marketplace',
      lp_hero_title: 'Sell straight from the <span>harvest</span> — no middleman in between.',
      lp_hero_lead: 'Govi Paura connects farmer co-operatives directly with retailers and restaurants — transparent pricing, two-sided ratings, and delivery coordinated over existing lorry routes.',
      lp_join_farmer: 'Join as a Farmer',
      lp_join_buyer: 'Join as a Buyer',
      lp_stat_margin: 'Middleman margin removed',
      lp_stat_roles: 'Roles: Farmer, Buyer, Rider, Admin',
      lp_stat_lang: 'Sinhala-first interface',
      lp_flow1_t: "Farmer posts today's harvest",
      lp_flow1_s: 'Crop, quantity, price, photo',
      lp_flow2_t: 'Buyer bids or buys at fixed price',
      lp_flow2_s: 'Transparent, competitive offers',
      lp_flow3_t: 'Rider delivers on an existing route',
      lp_flow3_s: 'Shared lorry network, no new fleet',

      // Landing page — how it works
      lp_kicker_how: 'How It Works',
      lp_how_title: 'From farm gate to retail shelf in three steps',
      lp_how_sub: "The same core loop every listing follows — simple enough for a farmer with a feature phone, transparent enough for a buyer comparing prices.",
      lp_step1_t: 'List the Harvest',
      lp_step1_p: "A farmer or co-op posts today's crop, quantity and price — fixed or open to bidding — via the web app or a simple SMS command.",
      lp_step2_t: 'Negotiate & Confirm',
      lp_step2_p: 'Retailers and restaurants browse listings, place a bid or buy outright. The farmer accepts, and an order is created automatically.',
      lp_step3_t: 'Deliver & Rate',
      lp_step3_p: 'A delivery rider picks up and drops off the order on an existing route. Both sides rate each other, building trust over time.',

      // Landing page — portals
      lp_kicker_portals: 'One Platform, Four Portals',
      lp_portals_title: 'Built for everyone in the chain',
      lp_portals_sub: 'Each role gets a dashboard tailored to what they actually need to do — no clutter, no irrelevant menus.',
      lp_portal_farmer_p: 'List harvests, review bids, track orders and see fair-price trends.',
      lp_portal_buyer_p: 'Browse listings, place bids, check out orders and rate farmers.',
      lp_portal_rider_title: 'Delivery Rider',
      lp_portal_rider_p: 'Accept deliveries, update status, and manage route subscriptions.',
      lp_portal_admin_p: 'Verify users, moderate listings, and oversee platform activity.',

      // Landing page — features
      lp_kicker_features: 'Why Govi Paura',
      lp_features_title: 'Everything the old WhatsApp-and-a-mudalali process was missing',
      lp_features_sub: 'Each feature maps directly to a gap in the current informal trading process.',
      lp_feat1_t: 'Live Price History',
      lp_feat1_p: 'Aggregated, anonymised price trends by crop and region, so no one trades blind.',
      lp_feat2_t: 'Two-Sided Ratings',
      lp_feat2_p: 'Farmers and buyers rate each other after every order, building a real trust score.',
      lp_feat3_t: 'AI Chatbot Assistant',
      lp_feat3_p: 'Post a listing, check an order, or ask for a price trend in plain Sinhala or English.',
      lp_feat4_t: 'SMS Fallback',
      lp_feat4_p: 'No smartphone or data? Post a harvest listing with a simple structured text message.',
      lp_feat5_t: 'Route-Aware Delivery',
      lp_feat5_p: 'Deliveries are matched to existing lorry routes instead of requiring a new fleet.',
      lp_feat6_t: 'Sinhala-First Design',
      lp_feat6_p: 'Every screen is built Sinhala-first with English as a toggle, not an afterthought.',

      // Landing page — CTA + footer
      lp_cta_title: 'Ready to cut out the middleman?',
      lp_cta_sub: 'Create an account as a Farmer, Buyer or Delivery Rider — it takes less than two minutes.',
      lp_cta_create: 'Create Free Account',
      lp_cta_have: 'I Already Have an Account',
      lp_footer_about: 'A web-based B2B marketplace connecting farmer co-operatives directly with retailers and restaurants across Sri Lanka.',
      lp_footer_portals: 'Portals',
      lp_footer_register: 'Register',
      lp_footer_support: 'Support',
      lp_footer_copyright: 'Academic final-year project.',
      lp_footer_tagline: 'Made for farmers, buyers & riders across Sri Lanka.',
    },
    si: {
      notifications: 'දැනුම්දීම්',
      logout: 'ඉවත් වන්න',
      signedInAs: 'පිවිසී ඇත්තේ',

      nav_dashboard: 'මුල් පිටුව',
      nav_listings: 'මගේ ලැයිස්තු',
      nav_bids: 'ලැබුණු ලංසු',
      nav_orders: 'ඇණවුම්',
      nav_price_trends: 'මිල ප්‍රවණතා',
      nav_ratings: 'ශ්‍රේණිගත කිරීම් සහ සමාලෝචන',
      nav_notifications: 'දැනුම්දීම්',
      nav_helpdesk: 'සහාය මධ්‍යස්ථානය',
      nav_profile: 'පැතිකඩ',
      nav_account: 'ගිණුම',

      nav_available_deliveries: 'ලබාගත හැකි බෙදාහැරීම්',
      nav_assigned_deliveries: 'පවරන ලද බෙදාහැරීම්',
      nav_delivery_history: 'බෙදාහැරීම් ඉතිහාසය',
      nav_subscription: 'දායකත්වය',

      nav_browse_listings: 'නිෂ්පාදන පිරික්සන්න',
      nav_my_bids: 'මගේ ලංසු',
      nav_cart_checkout: 'කරත්තය සහ ගෙවීම',
      nav_my_orders: 'මගේ ඇණවුම්',

      nav_moderation: 'මධ්‍යස්ථභාවය',
      nav_system: 'පද්ධතිය',
      nav_verify_users: 'පරිශීලකයින් සත්‍යාපනය',
      nav_manage_users: 'පරිශීලකයින් කළමනාකරණය',
      nav_manage_listings: 'ලැයිස්තු කළමනාකරණය',
      nav_manage_bids_orders: 'ලංසු සහ ඇණවුම්',
      nav_manage_reviews: 'සමාලෝචන',
      nav_complaints: 'පැමිණිලි',
      nav_reports: 'වාර්තා',
      nav_system_logs: 'පද්ධති සටහන්',

      btn_save: 'වෙනස්කම් සුරකින්න',
      btn_cancel: 'අවලංගු කරන්න',
      btn_submit: 'ඉදිරිපත් කරන්න',
      btn_login: 'පිවිසෙන්න',
      btn_create_account: 'ගිණුමක් සාදන්න',
      btn_search_ph: 'සොයන්න...',

      status_active: 'ක්‍රියාකාරී',
      status_pending: 'අපේක්ෂිත',
      status_completed: 'සම්පූර්ණයි',
      status_verified: 'සත්‍යාපිතයි',
      status_suspended: 'අත්හිටුවා ඇත',

      role_farmer: 'ගොවියා',
      role_buyer: 'ගැනුම්කරු',
      role_rider: 'රියදුරු',
      role_admin: 'පරිපාලක',

      auth_login_quote: '"එක් ගිණුමක්, සෑම භූමිකාවක්ම — ගොවීන්, ගැනුම්කරුවන්, රියදුරන් සහ පරිපාලකයින් සියල්ලෝම මෙතැනින් පිවිසෙති."',
      auth_login_quote_sub: 'ඔබ පිවිසුණු පසු අපි ඔබව සෘජුවම ඔබේ පුවරුවට ගෙන යන්නෙමු.',
      auth_login_title: 'නැවත සාදරයෙන් පිළිගනිමු',
      auth_login_sub: 'ඔබ ලියාපදිංචි කළ ගිණුමෙන් පිවිසෙන්න — ගොවියෙකු, ගැනුම්කරුවෙකු, රියදුරෙකු හෝ පරිපාලකයෙකු ලෙස.',
      auth_identifier_label: 'ජංගම දුරකථන අංකය හෝ විද්‍යුත් තැපෑල',
      auth_password_label: 'මුරපදය',
      auth_otp_label: '2FA කේතය (පරිපාලක ගිණුම් සඳහා පමණි)',
      auth_remember: 'මතක තබාගන්න',
      auth_no_account: 'Govi Paura වෙත අලුත්ද?',
      auth_create_account_link: 'ගිණුමක් සාදන්න',
      auth_register_quote: '"අස්වනු ගමනේ ඔබේ භූමිකාව කුමක් වුවත්, ඔබ ආරම්භ කරන්නේ මෙතැනින්."',
      auth_register_quote_sub: 'ගොවීන් සහ රියදුරන් වෙළඳාම් කිරීමට හෝ බෙදාහැරීමට පෙර ජා.හැ.කො මගින් සත්‍යාපනය කරනු ලැබේ. ගැනුම්කරු ගිණුම් ක්ෂණිකව සූදානම්.',
      auth_register_title: 'ඔබේ ගිණුම සාදන්න',
      auth_register_sub: 'ඔබ Govi Paura භාවිතා කරන ආකාරය තෝරන්න — පසුව ඔබේ පැතිකඩෙන් තවත් භූමිකාවක් එක් කළ හැක.',
      auth_role_label: 'මම වන්නේ...',
      auth_preferred_language: 'කැමති භාෂාව',
      language_english: 'ඉංග්‍රීසි',
      language_sinhala: 'සිංහල',
      auth_full_name: 'සම්පූර්ණ නම',
      auth_phone: 'ජංගම දුරකථන අංකය',
      auth_email: 'විද්‍යුත් තැපෑල',
      auth_nic: 'ජාතික හැඳුනුම්පත් අංකය',
      auth_farm_location: 'ගොවිපල පිහිටීම',
      auth_nic_upload: 'ජා.හැ.කො උඩුගත කරන්න (ඉදිරිපස සහ පිටුපස)',
      auth_upload_hint: 'උඩුගත කිරීමට ක්ලික් කරන්න — JPG, PNG හෝ PDF, උපරිම 5MB',
      auth_vehicle_type: 'වාහන වර්ගය',
      auth_vehicle_number: 'වාහන ලියාපදිංචි අංකය',
      auth_vehicle_upload: 'වාහන ලියාපදිංචි ලේඛනය උඩුගත කරන්න',
      auth_business_name: 'ව්‍යාපාර නාමය (විකල්ප)',
      auth_delivery_address: 'පෙරනිමි බෙදාහැරීම් ලිපිනය',
      auth_confirm_password: 'මුරපදය තහවුරු කරන්න',
      auth_terms: 'මම Govi Paura සේවා නියම සහ පෞද්ගලිකත්ව ප්‍රතිපත්තියට එකඟ වෙමි.',
      auth_have_account: 'දැනටමත් ගිණුමක් තිබේද?',

      // Verification status
      verification_title: 'සත්‍යාපනය උඩුගත කර ඇත',
      verification_subtitle: 'ඔබේ අයැඩියයක් පරිපාලකයාගේ සමාලෝචනයේ සඳහා රැික්ෂා කරන්න đang සිටිනු ඇත.',
      verification_message: 'පරිපාලකයා ඔබේ අයැඩිය සමාලෝචනය කරන සඳහා උපරිම 24 පැය අවශ්‍ය olabilirය.',
      verification_status: 'තත්ත්වය',
      verification_pending: 'සත්‍යාපනය අරීත',
      verification_return: 'පිවිසේෂයටត្រឡប់ក្រៅ',

      // Landing page — nav
      lp_nav_how: 'ක්‍රියා කරන ආකාරය',
      lp_nav_portals: 'ද්වාර',
      lp_nav_features: 'විශේෂාංග',
      lp_get_started: 'ආරම්භ කරන්න',

      // Landing page — hero
      lp_eyebrow: 'වෙබ් පදනම් වූ B2B ගොවිපොළෙන් සිල්ලර වෙළඳපොළට',
      lp_hero_title: '<span>ඔබේ අස්වැන්න</span> කෙලින්ම විකුණන්න — මැදහත්කරුවෙකු නැතිව.',
      lp_hero_lead: 'Govi Paura ගොවි සමූහ සමිති සිල්ලර වෙළෙන්දන් සහ අවන්හල් සමඟ සෘජුව සම්බන්ධ කරයි. විනිවිද පෙනෙන මිල ගණන්, දෙපාර්ශවික ශ්‍රේණිගත කිරීම් සහ පවතින ලොරි මාර්ග හරහා පහසු බෙදාහැරීම් — සියල්ල එකම වේදිකාවකින්.',
      lp_join_farmer: 'ගොවියෙකු ලෙස එකතු වන්න',
      lp_join_buyer: 'ගැනුම්කරුවෙකු ලෙස එකතු වන්න',
      lp_stat_margin: 'ඉවත් කළ මැදහත්කරු ලාභය',
      lp_stat_roles: 'භූමිකා 4ක්: ගොවියා, ගැනුම්කරු, රියදුරු, පරිපාලක',
      lp_stat_lang: 'සිංහල-ප්‍රමුඛ අතුරු මුහුණත',
      lp_flow1_t: 'ගොවියා අද අස්වැන්න පළ කරයි',
      lp_flow1_s: 'බෝගය, ප්‍රමාණය, මිල, ඡායාරූපය',
      lp_flow2_t: 'ගැනුම්කරු ලංසු තබයි හෝ නියත මිලට ගනී',
      lp_flow2_s: 'විනිවිද පෙනෙන, තරඟකාරී පිරිනැමීම්',
      lp_flow3_t: 'රියදුරු පවතින මාර්ගයක බෙදා දෙයි',
      lp_flow3_s: 'බෙදාගත් ලොරි ජාලය, නව වාහන අවශ්‍ය නැත',

      // Landing page — how it works
      lp_kicker_how: 'ක්‍රියා කරන ආකාරය',
      lp_how_title: 'ගොවිපොළේ සිට සිල්ලර රාක්කය දක්වා පියවර තුනකින්',
      lp_how_sub: 'සෑම ලැයිස්තුවක්ම අනුගමනය කරන එකම මූලික ක්‍රියාවලිය — විශේෂාංග දුරකථනයක් ඇති ගොවියෙකුට තරම් සරලයි, මිල සංසන්දනය කරන ගැනුම්කරුවෙකුට තරම් විනිවිදභාවයි.',
      lp_step1_t: 'අස්වැන්න ලැයිස්තුගත කරන්න',
      lp_step1_p: 'ගොවියෙකු හෝ සමූහ සමිතියක් අද බෝගය, ප්‍රමාණය සහ මිල පළ කරයි — නියත මිලකට හෝ ලංසු තැබීමට විවෘතව — වෙබ් යෙදුම හෝ සරල SMS විධානයක් හරහා.',
      lp_step2_t: 'සාකච්ඡා කර තහවුරු කරන්න',
      lp_step2_p: 'සිල්ලර වෙළෙන්දෝ සහ අවන්හල් ලැයිස්තු පිරික්සා ලංසුවක් තබයි හෝ කෙලින්ම මිලට ගනී. ගොවියා පිළිගනී, ඇණවුමක් ස්වයංක්‍රීයව සාදනු ලැබේ.',
      lp_step3_t: 'බෙදා දී ශ්‍රේණිගත කරන්න',
      lp_step3_p: 'බෙදාහැරීමේ රියදුරෙකු පවතින මාර්ගයක ඇණවුම රැගෙන බෙදා දෙයි. දෙපාර්ශවයම එකිනෙකා ශ්‍රේණිගත කර කාලයත් සමඟ විශ්වාසය ගොඩනඟයි.',

      // Landing page — portals
      lp_kicker_portals: 'එක් වේදිකාවක්, ද්වාර හතරක්',
      lp_portals_title: 'දාමයේ සෑම කෙනෙකු සඳහාම නිර්මාණය කර ඇත',
      lp_portals_sub: 'සෑම භූමිකාවකටම එයට අවශ්‍ය දේට සරිලන පුවරුවක් ලැබේ — අනවශ්‍ය දෑ නැත, අදාළ නොවන මෙනු නැත.',
      lp_portal_farmer_p: 'අස්වනු ලැයිස්තුගත කරන්න, ලංසු පිරික්සන්න, ඇණවුම් නිරීක්ෂණය කර සාධාරණ මිල ප්‍රවණතා බලන්න.',
      lp_portal_buyer_p: 'ලැයිස්තු පිරික්සන්න, ලංසු තබන්න, ඇණවුම් ගෙවා ගොවීන් ශ්‍රේණිගත කරන්න.',
      lp_portal_rider_title: 'බෙදාහැරීමේ රියදුරු',
      lp_portal_rider_p: 'බෙදාහැරීම් පිළිගන්න, තත්ත්වය යාවත්කාලීන කර මාර්ග දායකත්ව කළමනාකරණය කරන්න.',
      lp_portal_admin_p: 'පරිශීලකයින් සත්‍යාපනය කරන්න, ලැයිස්තු මධ්‍යස්ථ කර වේදිකා ක්‍රියාකාරකම් අධීක්ෂණය කරන්න.',

      // Landing page — features
      lp_kicker_features: 'Govi Paura තෝරාගන්නේ ඇයි',
      lp_features_title: 'පැරණි WhatsApp-සහ-මුදලාලි ක්‍රියාවලියේ නොතිබූ සියල්ල',
      lp_features_sub: 'සෑම විශේෂාංගයක්ම වර්තමාන අනියම් වෙළඳ ක්‍රියාවලියේ හිඩැසකට කෙලින්ම සම්බන්ධයි.',
      lp_feat1_t: 'සජීවී මිල ඉතිහාසය',
      lp_feat1_p: 'බෝගය සහ කලාපය අනුව සමස්ත, නිර්නාමික මිල ප්‍රවණතා, කිසිවෙකු අන්ධව වෙළඳාම් නොකරන පරිදි.',
      lp_feat2_t: 'දෙපාර්ශවික ශ්‍රේණිගත කිරීම්',
      lp_feat2_p: 'ගොවීන් සහ ගැනුම්කරුවන් සෑම ඇණවුමකින්ම පසු එකිනෙකා ශ්‍රේණිගත කරයි, සැබෑ විශ්වාස ලකුණක් ගොඩනඟයි.',
      lp_feat3_t: 'AI චැට්බෝට් සහායක',
      lp_feat3_p: 'ලැයිස්තුවක් පළ කරන්න, ඇණවුමක් පිරික්සන්න, හෝ සිංහල හෝ ඉංග්‍රීසි භාෂාවෙන් මිල ප්‍රවණතාවක් අසන්න.',
      lp_feat4_t: 'SMS විසඳුම',
      lp_feat4_p: 'ස්මාර්ට් දුරකථනයක් හෝ දත්තයක් නැද්ද? සරල ව්‍යුහගත පණිවිඩයකින් අස්වනු ලැයිස්තුවක් පළ කරන්න.',
      lp_feat5_t: 'මාර්ග-දැනුවත් බෙදාහැරීම',
      lp_feat5_p: 'නව වාහන කණ්ඩායමක් අවශ්‍ය නොවී, බෙදාහැරීම් පවතින ලොරි මාර්ග සමඟ ගැලපේ.',
      lp_feat6_t: 'සිංහල-ප්‍රමුඛ නිර්මාණය',
      lp_feat6_p: 'සෑම තිරයක්ම නිර්මාණය කර ඇත්තේ සිංහල-ප්‍රමුඛව, ඉංග්‍රීසි යනු පසුව එකතු කළ දෙයක් නොව මාරු කළ හැකි විකල්පයක් ලෙසයි.',

      // Landing page — CTA + footer
      lp_cta_title: 'මැදහත්කරුවා ඉවත් කිරීමට සූදානම්ද?',
      lp_cta_sub: 'ගොවියෙකු, ගැනුම්කරුවෙකු හෝ බෙදාහැරීමේ රියදුරෙකු ලෙස ගිණුමක් සාදන්න — විනාඩි දෙකකටත් අඩු කාලයක් ගතවේ.',
      lp_cta_create: 'නොමිලේ ගිණුමක් සාදන්න',
      lp_cta_have: 'දැනටමත් ගිණුමක් තිබේ',
      lp_footer_about: 'ශ්‍රී ලංකාව පුරා ගොවි සමූහ සමිති සිල්ලර වෙළෙන්දන් සහ අවන්හල් සමඟ කෙලින්ම සම්බන්ධ කරන වෙබ් පදනම් B2B වෙළඳපොළක්.',
      lp_footer_portals: 'ද්වාර',
      lp_footer_register: 'ලියාපදිංචි වන්න',
      lp_footer_support: 'සහාය',
      lp_footer_copyright: 'අධ්‍යයන අවසාන වර්ෂයේ ව්‍යාපෘතියකි.',
      lp_footer_tagline: 'ශ්‍රී ලංකාව පුරා ගොවීන්, ගැනුම්කරුවන් සහ රියදුරන් සඳහා නිර්මාණය කරන ලදී.',
    },
  };

  function applyLang(lang) {
    document.documentElement.setAttribute('lang', lang === 'si' ? 'si' : 'en');
    document.querySelectorAll('[data-i18n]').forEach((el) => {
      const key = el.dataset.i18n;
      const val = DICT[lang] && DICT[lang][key];
      if (val) el.textContent = val;
    });
    // data-i18n-html: same idea, but the string may contain simple inline markup
    // (e.g. a <span> for a highlighted word) so it's applied via innerHTML instead.
    document.querySelectorAll('[data-i18n-html]').forEach((el) => {
      const key = el.dataset.i18nHtml;
      const val = DICT[lang] && DICT[lang][key];
      if (val) el.innerHTML = val;
    });
    document.querySelectorAll('[data-i18n-placeholder]').forEach((el) => {
      const key = el.dataset.i18nPlaceholder;
      const val = DICT[lang] && DICT[lang][key];
      if (val) el.setAttribute('placeholder', val);
    });
    document.querySelectorAll('[data-i18n-title]').forEach((el) => {
      const key = el.dataset.i18nTitle;
      const val = DICT[lang] && DICT[lang][key];
      if (val) { el.setAttribute('title', val); el.setAttribute('aria-label', val); }
    });
    document.querySelectorAll('[data-lang-switch]').forEach((btn) => {
      btn.classList.toggle('active', btn.dataset.langSwitch === lang);
    });
    try { localStorage.setItem('gp_lang', lang); } catch (e) { /* storage unavailable */ }
  }

  document.addEventListener('DOMContentLoaded', () => {
    let saved = 'en';
    try { saved = localStorage.getItem('gp_lang') || 'en'; } catch (e) { /* default to en */ }
    applyLang(saved);

    document.querySelectorAll('[data-lang-switch]').forEach((btn) => {
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        applyLang(btn.dataset.langSwitch);
      });
    });
  });

  window.gpI18n = { applyLang, DICT };
})();
