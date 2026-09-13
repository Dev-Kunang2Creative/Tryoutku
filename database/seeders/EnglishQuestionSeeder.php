<?php

namespace Database\Seeders;

/**
 * Bank soal Bahasa Inggris untuk jenjang SMP kelas 7-9.
 */
class EnglishQuestionSeeder extends QuestionBankSeeder
{
    protected function package(): array
    {
        return [
            'title' => 'Latihan Harian Bahasa Inggris',
            'slug' => 'latihan-harian-bahasa-inggris',
            'subject' => 'Bahasa Inggris',
            'description' => 'Bank soal Bahasa Inggris SMP kelas 7-9: grammar, kosakata, dan pemahaman teks pendek.',
            'duration_minutes' => 30,
            'questions_per_session' => 15,
        ];
    }

    protected function questions(): array
    {
        return [
            [
                'q' => "Choose the correct answer.\n\nMy brother and I ... students of SMP Negeri 2.",
                'options' => ['A' => 'am', 'B' => 'is', 'C' => 'are', 'D' => 'be', 'E' => 'was'],
                'key' => 'C',
                'explanation' => "Subjek \"My brother and I\" terdiri dari dua orang, jadi bersifat plural dan sama dengan \"we\".\n\nBentuk to be untuk subjek plural pada simple present adalah \"are\".\n\nJadi: My brother and I are students of SMP Negeri 2.",
            ],
            [
                'q' => "Choose the correct answer.\n\nRina ... to school by bus every morning.",
                'options' => ['A' => 'go', 'B' => 'goes', 'C' => 'going', 'D' => 'went', 'E' => 'is going'],
                'key' => 'B',
                'explanation' => "Kata \"every morning\" menandakan kebiasaan, jadi memakai simple present tense.\n\nSubjeknya \"Rina\" (orang ketiga tunggal), sehingga kata kerja harus ditambah -s atau -es.\n\nJadi: Rina goes to school by bus every morning.",
            ],
            [
                'q' => "Choose the correct answer.\n\nLook! The children ... football in the field now.",
                'options' => ['A' => 'play', 'B' => 'plays', 'C' => 'played', 'D' => 'are playing', 'E' => 'will play'],
                'key' => 'D',
                'explanation' => "Kata \"Look!\" dan \"now\" menunjukkan kejadian yang sedang berlangsung saat ini, jadi memakai present continuous tense.\n\nRumusnya: to be (am/is/are) + verb-ing. Subjek \"The children\" plural sehingga memakai \"are\".\n\nJadi: The children are playing football in the field now.",
            ],
            [
                'q' => "Choose the correct answer.\n\nWe ... a movie at the cinema last Sunday.",
                'options' => ['A' => 'watch', 'B' => 'watches', 'C' => 'watched', 'D' => 'watching', 'E' => 'will watch'],
                'key' => 'C',
                'explanation' => "Keterangan waktu \"last Sunday\" menunjukkan peristiwa yang sudah lewat, jadi memakai simple past tense.\n\nKata kerja \"watch\" adalah regular verb, bentuk past-nya cukup ditambah -ed menjadi \"watched\".\n\nJadi: We watched a movie at the cinema last Sunday.",
            ],
            [
                'q' => "Choose the correct answer.\n\nMy father ... to Surabaya yesterday.",
                'options' => ['A' => 'go', 'B' => 'goes', 'C' => 'goed', 'D' => 'went', 'E' => 'gone'],
                'key' => 'D',
                'explanation' => "Kata \"yesterday\" menunjukkan simple past tense.\n\nKata kerja \"go\" adalah irregular verb, sehingga bentuk past-nya bukan \"goed\" melainkan \"went\".\n\nJadi: My father went to Surabaya yesterday.",
            ],
            [
                'q' => "Choose the correct answer.\n\nI ... my homework, so I can play now.",
                'options' => ['A' => 'have finished', 'B' => 'has finished', 'C' => 'am finishing', 'D' => 'will finish', 'E' => 'finish'],
                'key' => 'A',
                'explanation' => "Pekerjaan sudah selesai dan hasilnya berpengaruh pada saat ini (\"so I can play now\"), jadi memakai present perfect tense.\n\nRumusnya: have/has + verb 3. Subjek \"I\" memakai \"have\".\n\nJadi: I have finished my homework.",
            ],
            [
                'q' => "Choose the correct answer.\n\nDon't worry. I ... you with your project tomorrow.",
                'options' => ['A' => 'helped', 'B' => 'am helping', 'C' => 'will help', 'D' => 'have helped', 'E' => 'helps'],
                'key' => 'C',
                'explanation' => "Kata \"tomorrow\" menunjukkan waktu yang akan datang, jadi memakai simple future tense.\n\nRumusnya: will + verb 1.\n\nJadi: I will help you with your project tomorrow.",
            ],
            [
                'q' => "Choose the correct answer.\n\nAn elephant is ... than a horse.",
                'options' => ['A' => 'big', 'B' => 'bigger', 'C' => 'biggest', 'D' => 'the biggest', 'E' => 'more big'],
                'key' => 'B',
                'explanation' => "Kata \"than\" adalah penanda comparative degree (membandingkan dua hal).\n\nUntuk kata sifat pendek seperti \"big\", comparative dibentuk dengan menambah -er. Karena berakhiran konsonan-vokal-konsonan, huruf akhirnya digandakan menjadi \"bigger\".\n\nJadi: An elephant is bigger than a horse.",
            ],
            [
                'q' => "Choose the correct answer.\n\nMount Everest is ... mountain in the world.",
                'options' => ['A' => 'high', 'B' => 'higher', 'C' => 'the highest', 'D' => 'more high', 'E' => 'as high as'],
                'key' => 'C',
                'explanation' => "Frasa \"in the world\" menunjukkan perbandingan dengan semua yang lain, jadi memakai superlative degree.\n\nSuperlative untuk kata sifat pendek dibentuk dengan \"the\" + adjective + -est.\n\nJadi: Mount Everest is the highest mountain in the world.",
            ],
            [
                'q' => "Choose the correct answer.\n\nThis book is very interesting. I really like ... .",
                'options' => ['A' => 'he', 'B' => 'it', 'C' => 'its', 'D' => 'they', 'E' => 'them'],
                'key' => 'B',
                'explanation' => "Kata ganti menggantikan \"this book\", yaitu benda tunggal, dan posisinya sebagai objek setelah kata kerja \"like\".\n\nObject pronoun untuk benda tunggal adalah \"it\".\n\nJadi: I really like it.",
            ],
            [
                'q' => "Choose the correct answer.\n\nThat is Dina. ... bag is on the table.",
                'options' => ['A' => 'She', 'B' => 'Her', 'C' => 'Hers', 'D' => 'She is', 'E' => 'Him'],
                'key' => 'B',
                'explanation' => "Kata yang dibutuhkan menerangkan kepemilikan dan diikuti kata benda \"bag\", jadi memakai possessive adjective.\n\nPossessive adjective untuk perempuan adalah \"her\". Kata \"hers\" adalah possessive pronoun yang tidak diikuti kata benda.\n\nJadi: Her bag is on the table.",
            ],
            [
                'q' => "Choose the correct answer.\n\nMy mother buys ... umbrella at the market.",
                'options' => ['A' => 'a', 'B' => 'an', 'C' => 'the', 'D' => 'some', 'E' => 'any'],
                'key' => 'B',
                'explanation' => "Artikel \"an\" dipakai sebelum kata benda tunggal yang diawali bunyi vokal.\n\nKata \"umbrella\" diawali bunyi vokal /a/, sehingga memakai \"an\".\n\nJadi: My mother buys an umbrella at the market.",
            ],
            [
                'q' => "Choose the correct answer.\n\nThe cat is sleeping ... the sofa.",
                'options' => ['A' => 'on', 'B' => 'in', 'C' => 'at', 'D' => 'of', 'E' => 'between'],
                'key' => 'A',
                'explanation' => "Preposisi \"on\" dipakai untuk sesuatu yang berada di atas permukaan dan bersentuhan dengannya.\n\nKucing tidur di atas permukaan sofa, jadi memakai \"on\".\n\nJadi: The cat is sleeping on the sofa.",
            ],
            [
                'q' => "Choose the correct answer.\n\nWe always have a flag ceremony ... Monday morning.",
                'options' => ['A' => 'in', 'B' => 'at', 'C' => 'on', 'D' => 'for', 'E' => 'since'],
                'key' => 'C',
                'explanation' => "Aturan preposisi waktu:\n- \"in\" untuk bulan, tahun, dan bagian hari (in the morning)\n- \"on\" untuk hari dan tanggal\n- \"at\" untuk jam\n\nKarena disebutkan nama hari (Monday morning), preposisi yang tepat adalah \"on\".",
            ],
            [
                'q' => "Choose the correct answer.\n\nMy little sister ... swim very well. She practises every week.",
                'options' => ['A' => 'must', 'B' => 'can', 'C' => 'should', 'D' => 'may', 'E' => 'would'],
                'key' => 'B',
                'explanation' => "Modal \"can\" menyatakan kemampuan (ability).\n\nKalimat menjelaskan bahwa adiknya mampu berenang dengan baik, jadi memakai \"can\".\n\nJadi: My little sister can swim very well.",
            ],
            [
                'q' => "Choose the correct answer.\n\nStudents ... wear uniforms at school. It is a school rule.",
                'options' => ['A' => 'may', 'B' => 'can', 'C' => 'must', 'D' => 'might', 'E' => 'could'],
                'key' => 'C',
                'explanation' => "Modal \"must\" menyatakan keharusan atau kewajiban yang kuat.\n\nKarena kalimat berikutnya menyebut \"It is a school rule\", yang dimaksud adalah kewajiban, sehingga memakai \"must\".",
            ],
            [
                'q' => "Choose the correct answer.\n\nYou look pale. You ... see a doctor.",
                'options' => ['A' => 'should', 'B' => 'must not', 'C' => 'can', 'D' => 'will', 'E' => 'may not'],
                'key' => 'A',
                'explanation' => "Modal \"should\" dipakai untuk memberi saran atau nasihat.\n\nKalimat ini menyarankan agar orang yang terlihat pucat pergi ke dokter, jadi memakai \"should\".\n\nJadi: You should see a doctor.",
            ],
            [
                'q' => "Complete the dialogue.\n\nAndi: \"... is your school?\"\nBudi: \"It is on Merdeka Street.\"",
                'options' => ['A' => 'What', 'B' => 'Who', 'C' => 'When', 'D' => 'Where', 'E' => 'Why'],
                'key' => 'D',
                'explanation' => "Jawaban \"It is on Merdeka Street\" menyebutkan lokasi.\n\nQuestion word untuk menanyakan tempat adalah \"where\".\n\nJadi: Where is your school?",
            ],
            [
                'q' => "Complete the dialogue.\n\nTeacher: \"... are you absent today?\"\nStudent: \"Because I have a fever, Ma'am.\"",
                'options' => ['A' => 'What', 'B' => 'Why', 'C' => 'Where', 'D' => 'Who', 'E' => 'How many'],
                'key' => 'B',
                'explanation' => "Jawaban dimulai dengan \"Because\", yang berarti menyatakan alasan.\n\nQuestion word untuk menanyakan alasan adalah \"why\".\n\nJadi: Why are you absent today?",
            ],
            [
                'q' => "Choose the correct plural form.\n\nThere are three ... in the science laboratory.",
                'options' => ['A' => 'mouses', 'B' => 'mice', 'C' => 'mouse', 'D' => 'mices', 'E' => 'mouse\'s'],
                'key' => 'B',
                'explanation' => "Kata \"mouse\" adalah irregular noun, sehingga bentuk pluralnya tidak dengan menambah -s.\n\nBentuk plural dari \"mouse\" adalah \"mice\".\n\nContoh irregular plural lain: child - children, foot - feet, tooth - teeth.",
            ],
            [
                'q' => "Choose the correct answer.\n\nHow ... sugar do you need for this cake?",
                'options' => ['A' => 'many', 'B' => 'much', 'C' => 'some', 'D' => 'a lot', 'E' => 'few'],
                'key' => 'B',
                'explanation' => "\"How many\" dipakai untuk kata benda yang dapat dihitung (countable), sedangkan \"how much\" untuk yang tidak dapat dihitung (uncountable).\n\nKata \"sugar\" adalah uncountable noun, jadi memakai \"much\".\n\nJadi: How much sugar do you need?",
            ],
            [
                'q' => "Choose the correct answer.\n\nMy father ... goes fishing on Sunday. He does it almost every week.",
                'options' => ['A' => 'never', 'B' => 'rarely', 'C' => 'usually', 'D' => 'once a year', 'E' => 'hardly ever'],
                'key' => 'C',
                'explanation' => "Kalimat menjelaskan bahwa ayahnya memancing hampir setiap minggu, artinya sering terjadi.\n\nAdverb of frequency yang menyatakan sering adalah \"usually\". Kata \"never\", \"rarely\", dan \"hardly ever\" berarti jarang atau tidak pernah.",
            ],
            [
                'q' => "Choose the correct answer.\n\n... a big library near my house.",
                'options' => ['A' => 'There is', 'B' => 'There are', 'C' => 'It are', 'D' => 'They is', 'E' => 'Have'],
                'key' => 'A',
                'explanation' => "Untuk menyatakan keberadaan sesuatu dipakai \"there is\" (benda tunggal) atau \"there are\" (benda plural).\n\nKarena \"a big library\" berbentuk tunggal, yang tepat adalah \"there is\".\n\nJadi: There is a big library near my house.",
            ],
            [
                'q' => "Choose the correct answer.\n\n\"... the door, please. It is very cold outside.\"",
                'options' => ['A' => 'Closing', 'B' => 'Closed', 'C' => 'Close', 'D' => 'To close', 'E' => 'Closes'],
                'key' => 'C',
                'explanation' => "Kalimat perintah (imperative) diawali langsung dengan kata kerja bentuk dasar (verb 1) tanpa subjek.\n\nJadi: Close the door, please.",
            ],
            [
                'q' => "Choose the correct answer.\n\nMy cousin ... a new bicycle. It is red and very fast.",
                'options' => ['A' => 'have', 'B' => 'has', 'C' => 'having', 'D' => 'is have', 'E' => 'are having'],
                'key' => 'B',
                'explanation' => "Subjek \"My cousin\" adalah orang ketiga tunggal, sehingga bentuk kata kerja \"have\" berubah menjadi \"has\" pada simple present tense.\n\nJadi: My cousin has a new bicycle.",
            ],
            [
                'q' => "Choose the correct answer.\n\nI don't have ... money in my pocket.",
                'options' => ['A' => 'some', 'B' => 'any', 'C' => 'many', 'D' => 'a', 'E' => 'few'],
                'key' => 'B',
                'explanation' => "Kata \"some\" umumnya dipakai dalam kalimat positif, sedangkan \"any\" dipakai dalam kalimat negatif dan pertanyaan.\n\nKalimat ini negatif (\"don't have\") dan \"money\" adalah uncountable, jadi memakai \"any\".\n\nJadi: I don't have any money in my pocket.",
            ],
            [
                'q' => "Choose the correct answer.\n\n... books on the shelf are mine.",
                'options' => ['A' => 'This', 'B' => 'That', 'C' => 'These', 'D' => 'It', 'E' => 'Its'],
                'key' => 'C',
                'explanation' => "Kata \"books\" berbentuk plural, jadi demonstrative-nya juga harus plural.\n\n\"This\" dan \"that\" untuk benda tunggal, sedangkan \"these\" (dekat) dan \"those\" (jauh) untuk benda plural.\n\nJadi: These books on the shelf are mine.",
            ],
            [
                'q' => "Choose the correct answer.\n\nShe was tired, ... she kept studying for the test.",
                'options' => ['A' => 'so', 'B' => 'because', 'C' => 'but', 'D' => 'and', 'E' => 'or'],
                'key' => 'C',
                'explanation' => "Kedua bagian kalimat berlawanan makna: dia lelah, tetapi tetap belajar.\n\nConjunction yang menyatakan pertentangan adalah \"but\".\n\nJadi: She was tired, but she kept studying for the test.",
            ],
            [
                'q' => "Choose the correct answer.\n\nMy sister likes ... in the garden every afternoon.",
                'options' => ['A' => 'garden', 'B' => 'gardens', 'C' => 'gardening', 'D' => 'to gardening', 'E' => 'gardened'],
                'key' => 'C',
                'explanation' => "Setelah kata kerja \"like\" dapat diikuti gerund (verb-ing) untuk menyatakan kegiatan yang disukai.\n\nJadi: My sister likes gardening in the garden every afternoon.",
            ],
            [
                'q' => "Choose the correct answer.\n\nThe coffee is ... hot for me to drink.",
                'options' => ['A' => 'enough', 'B' => 'too', 'C' => 'very much', 'D' => 'so many', 'E' => 'as'],
                'key' => 'B',
                'explanation' => "Pola \"too + adjective + to + verb\" berarti terlalu ... untuk melakukan sesuatu, menyatakan sesuatu yang berlebihan sehingga tidak bisa dilakukan.\n\nJadi: The coffee is too hot for me to drink.",
            ],
            [
                'q' => "What is the meaning of the underlined word?\n\nMy uncle is a DENTIST. He treats people's teeth.",
                'options' => ['A' => 'Dokter mata', 'B' => 'Dokter gigi', 'C' => 'Perawat', 'D' => 'Apoteker', 'E' => 'Dokter hewan'],
                'key' => 'B',
                'explanation' => "Kata \"dentist\" berarti dokter gigi.\n\nPetunjuknya ada pada kalimat kedua: \"He treats people's teeth\" (dia merawat gigi orang).",
            ],
            [
                'q' => "Choose the correct answer.\n\nMy mother's sister is my ... .",
                'options' => ['A' => 'niece', 'B' => 'cousin', 'C' => 'aunt', 'D' => 'uncle', 'E' => 'grandmother'],
                'key' => 'C',
                'explanation' => "Saudara perempuan dari ibu atau ayah kita disebut \"aunt\" (bibi atau tante).\n\n\"Uncle\" adalah saudara laki-laki orang tua, \"cousin\" adalah anak dari aunt/uncle, dan \"niece\" adalah keponakan perempuan.",
            ],
            [
                'q' => "Choose the correct answer.\n\nWe use a ... to draw a straight line in mathematics class.",
                'options' => ['A' => 'eraser', 'B' => 'ruler', 'C' => 'glue', 'D' => 'scissors', 'E' => 'globe'],
                'key' => 'B',
                'explanation' => "\"Ruler\" berarti penggaris, alat yang dipakai untuk menggambar garis lurus.\n\n\"Eraser\" adalah penghapus, \"glue\" adalah lem, \"scissors\" adalah gunting, dan \"globe\" adalah bola dunia.",
            ],
            [
                'q' => "Choose the correct answer.\n\nIt is raining heavily. You need an ... .",
                'options' => ['A' => 'umbrella', 'B' => 'apron', 'C' => 'envelope', 'D' => 'onion', 'E' => 'anchor'],
                'key' => 'A',
                'explanation' => 'Saat hujan turun deras, benda yang diperlukan adalah payung, yang dalam bahasa Inggris disebut "umbrella".',
            ],
            [
                'q' => "Choose the correct answer.\n\nA ... is an animal that gives us milk.",
                'options' => ['A' => 'hen', 'B' => 'cow', 'C' => 'sheep', 'D' => 'duck', 'E' => 'bee'],
                'key' => 'B',
                'explanation' => "\"Cow\" berarti sapi, hewan yang menghasilkan susu.\n\n\"Hen\" (ayam betina) dan \"duck\" (bebek) menghasilkan telur, \"sheep\" (domba) menghasilkan wol, dan \"bee\" (lebah) menghasilkan madu.",
            ],
            [
                'q' => "Choose the correct answer.\n\nBreakfast is the meal we eat in the ... .",
                'options' => ['A' => 'morning', 'B' => 'afternoon', 'C' => 'evening', 'D' => 'night', 'E' => 'midnight'],
                'key' => 'A',
                'explanation' => "\"Breakfast\" adalah makan pagi, yaitu makanan yang disantap pada pagi hari (in the morning).\n\n\"Lunch\" dimakan siang hari dan \"dinner\" dimakan pada malam hari.",
            ],
            [
                'q' => "Choose the correct answer.\n\nWe keep our clothes in a ... .",
                'options' => ['A' => 'refrigerator', 'B' => 'wardrobe', 'C' => 'sink', 'D' => 'stove', 'E' => 'bookshelf'],
                'key' => 'B',
                'explanation' => "\"Wardrobe\" berarti lemari pakaian.\n\n\"Refrigerator\" adalah kulkas, \"sink\" adalah bak cuci, \"stove\" adalah kompor, dan \"bookshelf\" adalah rak buku.",
            ],
            [
                'q' => "Choose the correct answer.\n\nThe opposite of the word \"expensive\" is ... .",
                'options' => ['A' => 'costly', 'B' => 'cheap', 'C' => 'rich', 'D' => 'valuable', 'E' => 'high'],
                'key' => 'B',
                'explanation' => "Kata \"expensive\" berarti mahal.\n\nAntonim atau lawan katanya adalah \"cheap\" yang berarti murah. Kata \"costly\" dan \"valuable\" justru bersinonim dengan mahal atau bernilai.",
            ],
            [
                'q' => "Choose the correct answer.\n\nWhat time is it? The clock shows 07.15. It is ... .",
                'options' => [
                    'A' => 'seven o\'clock',
                    'B' => 'a quarter past seven',
                    'C' => 'a quarter to seven',
                    'D' => 'half past seven',
                    'E' => 'a quarter past eight',
                ],
                'key' => 'B',
                'explanation' => "Angka 15 menit setelah suatu jam disebut \"a quarter past\".\n\nJadi 07.15 dibaca \"a quarter past seven\".\n\nUntuk 07.30 dibaca \"half past seven\", dan 06.45 dibaca \"a quarter to seven\".",
            ],
            [
                'q' => "Read the notice and answer the question.\n\nNOTICE\nKEEP SILENT\nExamination in progress\n\nWhere do we usually find this notice?",
                'options' => [
                    'A' => 'In the canteen',
                    'B' => 'In the school yard',
                    'C' => 'Near the examination room',
                    'D' => 'At the bus station',
                    'E' => 'In the sport field',
                ],
                'key' => 'C',
                'explanation' => "Pemberitahuan tersebut meminta orang untuk tetap tenang karena ada ujian yang sedang berlangsung (\"Examination in progress\").\n\nJadi tempat yang paling tepat untuk pemberitahuan itu adalah di dekat ruang ujian.",
            ],
            [
                'q' => "Read the text and answer the question.\n\nDear Sinta,\nI would like to invite you to my birthday party.\nDay: Saturday, 20 July\nTime: 04.00 p.m.\nPlace: My house, Jalan Kartini No. 12\n\nYours,\nMaya\n\nWhat kind of text is it?",
                'options' => ['A' => 'An announcement', 'B' => 'An invitation', 'C' => 'An advertisement', 'D' => 'A label', 'E' => 'A memo'],
                'key' => 'B',
                'explanation' => "Teks tersebut berisi ajakan untuk menghadiri pesta ulang tahun, ditandai dengan kalimat \"I would like to invite you\".\n\nTeks yang berisi ajakan menghadiri sebuah acara disebut invitation (undangan).",
            ],
            [
                'q' => "Read the text and answer the question.\n\nDear Sinta,\nI would like to invite you to my birthday party.\nDay: Saturday, 20 July\nTime: 04.00 p.m.\nPlace: My house, Jalan Kartini No. 12\n\nYours,\nMaya\n\nWhen will the party be held?",
                'options' => [
                    'A' => 'On Saturday afternoon',
                    'B' => 'On Sunday morning',
                    'C' => 'On Saturday morning',
                    'D' => 'On Friday evening',
                    'E' => 'On Monday afternoon',
                ],
                'key' => 'A',
                'explanation' => "Dalam undangan disebutkan Day: Saturday dan Time: 04.00 p.m.\n\nWaktu 04.00 p.m. berarti pukul 16.00 atau sore hari. Jadi pesta akan diadakan pada hari Sabtu sore (Saturday afternoon).",
            ],
            [
                'q' => "Read the announcement and answer the question.\n\nANNOUNCEMENT\nAll students of grade 8 must attend the English club meeting on Friday at 02.00 p.m. in the library.\n\nWho should attend the meeting?",
                'options' => [
                    'A' => 'All teachers',
                    'B' => 'All students of grade 7',
                    'C' => 'All students of grade 8',
                    'D' => 'The librarian only',
                    'E' => 'All students of the school',
                ],
                'key' => 'C',
                'explanation' => "Pengumuman itu diawali dengan \"All students of grade 8 must attend ...\".\n\nJadi yang harus menghadiri pertemuan adalah seluruh siswa kelas 8.",
            ],
            [
                'q' => "Read the label and answer the question.\n\nCHOCOLATE MILK\nNet weight: 250 ml\nKeep refrigerated\nBest before: 12 December 2026\n\nWhat should we do to keep the product fresh?",
                'options' => [
                    'A' => 'Put it in the refrigerator',
                    'B' => 'Keep it near the stove',
                    'C' => 'Put it under the sunlight',
                    'D' => 'Shake it every day',
                    'E' => 'Drink it after 12 December 2026',
                ],
                'key' => 'A',
                'explanation' => "Pada label terdapat petunjuk \"Keep refrigerated\" yang berarti simpan dalam keadaan dingin atau di dalam kulkas.\n\nJadi agar tetap segar, produk harus disimpan di dalam kulkas.",
            ],
            [
                'q' => "Read the text and answer the question.\n\nMy Grandmother\n\nMy grandmother is sixty-five years old. She is quite short and a little bit plump. She has short grey hair and kind brown eyes. She always wears glasses when she reads. She is very patient and loves cooking for her grandchildren.\n\nWhat does the text tell us about?",
                'options' => [
                    'A' => 'The writer\'s mother',
                    'B' => 'The writer\'s grandmother',
                    'C' => 'The writer\'s teacher',
                    'D' => 'The writer\'s neighbour',
                    'E' => 'The writer\'s aunt',
                ],
                'key' => 'B',
                'explanation' => "Judul teks adalah \"My Grandmother\" dan seluruh isinya menjelaskan ciri-ciri serta sifat nenek penulis.\n\nJadi teks tersebut menceritakan tentang nenek penulis.",
            ],
            [
                'q' => "Read the text and answer the question.\n\nMy Grandmother\n\nMy grandmother is sixty-five years old. She is quite short and a little bit plump. She has short grey hair and kind brown eyes. She always wears glasses when she reads. She is very patient and loves cooking for her grandchildren.\n\nWhen does she wear glasses?",
                'options' => [
                    'A' => 'When she sleeps',
                    'B' => 'When she cooks',
                    'C' => 'When she reads',
                    'D' => 'When she walks',
                    'E' => 'When she goes shopping',
                ],
                'key' => 'C',
                'explanation' => "Pada teks disebutkan \"She always wears glasses when she reads\".\n\nJadi nenek memakai kacamata ketika membaca.",
            ],
            [
                'q' => "Read the text and answer the question.\n\nMy Grandmother\n\nMy grandmother is sixty-five years old. She is quite short and a little bit plump. She has short grey hair and kind brown eyes. She always wears glasses when she reads. She is very patient and loves cooking for her grandchildren.\n\nThe word \"plump\" in the text is closest in meaning to ...",
                'options' => ['A' => 'thin', 'B' => 'tall', 'C' => 'chubby', 'D' => 'strong', 'E' => 'young'],
                'key' => 'C',
                'explanation' => "Kata \"plump\" berarti agak gemuk atau berisi.\n\nDi antara pilihan yang ada, kata yang paling dekat maknanya adalah \"chubby\". Kata \"thin\" justru berarti kurus, sehingga berlawanan makna.",
            ],
            [
                'q' => "Read the text and answer the question.\n\nLast holiday my family and I went to Yogyakarta. We visited Borobudur Temple in the morning. After that, we had lunch at a traditional restaurant near the temple. In the afternoon we bought some souvenirs at Malioboro Street. We went home in the evening. It was a wonderful trip.\n\nWhat kind of text is it?",
                'options' => ['A' => 'A descriptive text', 'B' => 'A recount text', 'C' => 'A procedure text', 'D' => 'A narrative text', 'E' => 'A report text'],
                'key' => 'B',
                'explanation' => "Teks tersebut menceritakan pengalaman yang sudah terjadi di masa lalu secara berurutan dan memakai simple past tense (went, visited, had, bought).\n\nTeks yang menceritakan pengalaman masa lalu disebut recount text.",
            ],
            [
                'q' => "Read the text and answer the question.\n\nLast holiday my family and I went to Yogyakarta. We visited Borobudur Temple in the morning. After that, we had lunch at a traditional restaurant near the temple. In the afternoon we bought some souvenirs at Malioboro Street. We went home in the evening. It was a wonderful trip.\n\nWhat did they do in the afternoon?",
                'options' => [
                    'A' => 'They visited Borobudur Temple',
                    'B' => 'They had lunch at a restaurant',
                    'C' => 'They bought some souvenirs',
                    'D' => 'They went home',
                    'E' => 'They took a rest at a hotel',
                ],
                'key' => 'C',
                'explanation' => "Pada teks disebutkan \"In the afternoon we bought some souvenirs at Malioboro Street\".\n\nJadi pada siang atau sore hari mereka membeli suvenir.",
            ],
            [
                'q' => "Read the text and answer the question.\n\nLast holiday my family and I went to Yogyakarta. We visited Borobudur Temple in the morning. After that, we had lunch at a traditional restaurant near the temple. In the afternoon we bought some souvenirs at Malioboro Street. We went home in the evening. It was a wonderful trip.\n\nWhat is the writer's impression of the trip?",
                'options' => [
                    'A' => 'It was boring',
                    'B' => 'It was tiring',
                    'C' => 'It was wonderful',
                    'D' => 'It was disappointing',
                    'E' => 'It was frightening',
                ],
                'key' => 'C',
                'explanation' => "Kalimat penutup teks adalah \"It was a wonderful trip\".\n\nJadi kesan penulis terhadap perjalanan itu adalah menyenangkan atau luar biasa (wonderful).",
            ],
            [
                'q' => "Read the text and answer the question.\n\nHow to Make a Cup of Tea\n\n1. Boil some water.\n2. Put a tea bag into a cup.\n3. Pour the hot water into the cup.\n4. Wait for three minutes.\n5. Add some sugar and stir it well.\n\nWhat is the purpose of the text?",
                'options' => [
                    'A' => 'To describe a cup of tea',
                    'B' => 'To tell a story about tea',
                    'C' => 'To explain how to make a cup of tea',
                    'D' => 'To advertise a tea product',
                    'E' => 'To report the history of tea',
                ],
                'key' => 'C',
                'explanation' => "Teks berisi langkah-langkah berurutan yang diawali kata kerja perintah (boil, put, pour, wait, add).\n\nTeks semacam ini disebut procedure text, dan tujuannya adalah menjelaskan cara membuat sesuatu, dalam hal ini cara membuat secangkir teh.",
            ],
            [
                'q' => "Read the text and answer the question.\n\nHow to Make a Cup of Tea\n\n1. Boil some water.\n2. Put a tea bag into a cup.\n3. Pour the hot water into the cup.\n4. Wait for three minutes.\n5. Add some sugar and stir it well.\n\nWhat should we do after pouring the hot water?",
                'options' => [
                    'A' => 'Boil some water',
                    'B' => 'Put a tea bag into a cup',
                    'C' => 'Wait for three minutes',
                    'D' => 'Drink the tea immediately',
                    'E' => 'Throw the tea bag away',
                ],
                'key' => 'C',
                'explanation' => "Menurut urutan langkah, setelah nomor 3 (\"Pour the hot water into the cup\") langkah berikutnya adalah nomor 4, yaitu \"Wait for three minutes\".\n\nJadi setelah menuang air panas kita harus menunggu selama tiga menit.",
            ],
            [
                'q' => "Choose the correct answer.\n\nIf it rains tomorrow, we ... stay at home.",
                'options' => ['A' => 'will', 'B' => 'would', 'C' => 'were', 'D' => 'had', 'E' => 'are'],
                'key' => 'A',
                'explanation' => "Kalimat ini adalah conditional sentence type 1, yaitu syarat yang mungkin terjadi.\n\nRumusnya: If + simple present, subject + will + verb 1.\n\nJadi: If it rains tomorrow, we will stay at home.",
            ],
            [
                'q' => "Choose the correct answer.\n\nThe classroom ... every morning by the students on duty.",
                'options' => ['A' => 'clean', 'B' => 'cleans', 'C' => 'is cleaned', 'D' => 'is cleaning', 'E' => 'cleaning'],
                'key' => 'C',
                'explanation' => "Subjek \"The classroom\" adalah benda yang dikenai pekerjaan, bukan yang melakukan, sehingga kalimatnya berbentuk pasif.\n\nRumus passive voice simple present: to be (am/is/are) + verb 3.\n\nJadi: The classroom is cleaned every morning by the students on duty.",
            ],
            [
                'q' => "Choose the correct answer.\n\nThis is the boy ... won the speech contest last week.",
                'options' => ['A' => 'which', 'B' => 'who', 'C' => 'whose', 'D' => 'where', 'E' => 'when'],
                'key' => 'B',
                'explanation' => "Relative pronoun \"who\" dipakai untuk menerangkan orang, sedangkan \"which\" untuk benda atau binatang.\n\nKarena yang diterangkan adalah \"the boy\" (orang), maka memakai \"who\".\n\nJadi: This is the boy who won the speech contest last week.",
            ],
            [
                'q' => "Choose the correct answer.\n\nWhile I ... my homework, my mother was cooking dinner.",
                'options' => ['A' => 'do', 'B' => 'did', 'C' => 'was doing', 'D' => 'have done', 'E' => 'will do'],
                'key' => 'C',
                'explanation' => "Dua kegiatan berlangsung bersamaan di masa lalu, ditandai kata \"while\" dan klausa \"was cooking\".\n\nKeduanya memakai past continuous tense: was/were + verb-ing.\n\nJadi: While I was doing my homework, my mother was cooking dinner.",
            ],
            [
                'q' => "Arrange these words into a good sentence.\n\nalways - my - the - father - newspaper - reads - morning - in\n\n1) always  2) my  3) the  4) father  5) newspaper  6) reads  7) morning  8) in",
                'options' => [
                    'A' => '2 - 4 - 1 - 6 - 3 - 5 - 8 - 3 - 7',
                    'B' => '2 - 4 - 1 - 6 - 3 - 5 - 8 - 7',
                    'C' => '4 - 2 - 6 - 1 - 5 - 3 - 8 - 7',
                    'D' => '1 - 2 - 4 - 6 - 5 - 3 - 7 - 8',
                    'E' => '2 - 4 - 6 - 1 - 3 - 5 - 7 - 8',
                ],
                'key' => 'B',
                'explanation' => "Susunan kalimat yang benar adalah:\nMy father always reads the newspaper in the morning.\n\nUrutan nomornya: my (2) - father (4) - always (1) - reads (6) - the (3) - newspaper (5) - in (8) - morning (7).\n\nAdverb of frequency seperti \"always\" diletakkan sebelum kata kerja utama.",
            ],
            [
                'q' => "Complete the dialogue.\n\nWaiter: \"Good evening. May I take your order?\"\nCustomer: \"Yes, ... a bowl of chicken soup, please.\"",
                'options' => ['A' => 'I am', 'B' => 'I would like', 'C' => 'I like to', 'D' => 'I have been', 'E' => 'I was'],
                'key' => 'B',
                'explanation' => "Untuk memesan makanan secara sopan di restoran, ungkapan yang lazim adalah \"I would like ...\" yang berarti saya ingin.\n\nJadi: I would like a bowl of chicken soup, please.",
            ],
            [
                'q' => "Complete the dialogue.\n\nRani: \"I got the first prize in the writing competition.\"\nDewi: \"... !\"",
                'options' => [
                    'A' => 'I am sorry to hear that',
                    'B' => 'What a pity',
                    'C' => 'Congratulations',
                    'D' => 'Never mind',
                    'E' => 'Get well soon',
                ],
                'key' => 'C',
                'explanation' => "Rani menyampaikan kabar baik bahwa ia menang lomba.\n\nUngkapan yang tepat untuk memberi selamat atas keberhasilan seseorang adalah \"Congratulations!\".\n\nUngkapan lain pada pilihan dipakai untuk menanggapi kabar buruk.",
            ],
            [
                'q' => "Choose the correct answer.\n\nMy shoes are dirty. I have to ... them before going to school.",
                'options' => ['A' => 'wash', 'B' => 'washed', 'C' => 'washing', 'D' => 'washes', 'E' => 'to washing'],
                'key' => 'A',
                'explanation' => "Setelah \"have to\" selalu diikuti kata kerja bentuk dasar (verb 1) tanpa perubahan.\n\nJadi: I have to wash them before going to school.",
            ],
        ];
    }
}
