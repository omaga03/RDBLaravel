{!! '<' . '?xml version="1.0" encoding="UTF-8"?' . '>' !!}
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <title>{{ $siteTitle }}</title>
        <link>{{ $siteLink }}</link>
        <description>{{ $siteDescription }}</description>
        <language>th-TH</language>
        <lastBuildDate>{{ $lastBuildDate }}</lastBuildDate>
        <generator>RDI PCRU Research RSS Generator</generator>
        <atom:link href="{{ url()->current() }}" rel="self" type="application/rss+xml" />

        @foreach($items as $row)
            @php
                $title = trim(strip_tags((string) $row->con_name));
                $link = route('frontend.researchcoferenceinthai.show', $row->id);
                $imgUrl = $row->image_url;
                $pubDate = null;
                if (!empty($row->con_even_date)) {
                    try {
                        $pubDate = \Carbon\Carbon::parse($row->con_even_date)->toRssString();
                    } catch (\Throwable $e) {
                        $pubDate = (string) $row->con_even_date;
                    }
                }
                $desc = $row->plain_summary ?: $title;
                if ($imgUrl) {
                    $desc = '<p><img src="' . htmlspecialchars($imgUrl) . '" alt="' . htmlspecialchars($title) . '" style="max-width:100%;"></p>' . '<p>' . htmlspecialchars($desc) . '</p>';
                }
            @endphp
            <item>
                <title>{{ $title }}</title>
                <link>{{ $link }}</link>
                <guid isPermaLink="true">{{ $link }}</guid>
                @if($pubDate)<pubDate>{{ $pubDate }}</pubDate>@endif
                @if($imgUrl)<imgUrl>{{ $imgUrl }}</imgUrl>@endif
                <newsCount>{{ (int) $row->con_count }}</newsCount>
                <description><![CDATA[{!! $desc !!}]]></description>
            </item>
        @endforeach
    </channel>
</rss>