<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

/** Starter FAQs describing how the system works. Edit them in Admin → FAQ. */
class FaqSeeder extends Seeder
{
    public function run(): void
    {
        if (Faq::exists()) {
            return;
        }

        $faqs = [
            ['Who can apply?', 'কে আবেদন করতে পারবেন?',
                'Bangladeshi citizens aged 18+ with a valid NID, an active mobile number and a regular income.',
                '১৮ বছর বা তার বেশি বয়সী বাংলাদেশি নাগরিক, যাঁদের বৈধ এনআইডি, সক্রিয় মোবাইল নম্বর ও নিয়মিত আয় আছে।'],
            ['Which documents do I need?', 'কী কী কাগজ লাগবে?',
                'Clear photos of the front and back of your NID. A selfie may be requested.',
                'আপনার এনআইডির সামনের ও পেছনের স্পষ্ট ছবি। প্রয়োজনে একটি সেলফি চাওয়া হতে পারে।'],
            ['Do I have to pay anything to apply?', 'আবেদন করতে কি কোনো টাকা দিতে হয়?',
                'No. Applying is free. Any processing fee is shown in the agreement before you accept, and it is never required before approval.',
                'না। আবেদন সম্পূর্ণ বিনামূল্যে। কোনো প্রসেসিং ফি থাকলে তা চুক্তিতে আগেই দেখানো হয় এবং অনুমোদনের আগে কখনো চাওয়া হয় না।'],
            ['Is approval guaranteed?', 'ঋণ পাওয়া কি নিশ্চিত?',
                'No. Every application is reviewed individually. Payments of any fee do not guarantee approval.',
                'না। প্রতিটি আবেদন আলাদাভাবে যাচাই করা হয়। কোনো ফি পরিশোধ করলেই ঋণ নিশ্চিত হয় না।'],
            ['How do I pay installments?', 'কিস্তি কীভাবে দেব?',
                'From your dashboard, open the loan and tap "Pay Installment". You can pay with bKash, Nagad, Rocket and other methods.',
                'ড্যাশবোর্ড থেকে ঋণটি খুলে "কিস্তি পরিশোধ করুন" চাপুন। বিকাশ, নগদ, রকেটসহ বিভিন্ন মাধ্যমে পরিশোধ করা যায়।'],
            ['Is my NID information safe?', 'আমার এনআইডির তথ্য কি নিরাপদ?',
                'Yes. NID numbers are encrypted and images are stored privately. Only authorised staff can view them.',
                'হ্যাঁ। এনআইডি নম্বর এনক্রিপ্ট করা থাকে এবং ছবিগুলো গোপনে সংরক্ষিত হয়। শুধু অনুমোদিত কর্মকর্তারা দেখতে পারেন।'],
        ];

        foreach ($faqs as $i => [$q, $qbn, $a, $abn]) {
            Faq::create(['question' => $q, 'question_bn' => $qbn, 'answer' => $a, 'answer_bn' => $abn, 'sort_order' => $i]);
        }
    }
}
