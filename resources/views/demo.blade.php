<html lang="en">
    <body>
        <h1>Hello there!</h1>

        <h4>Disease list:</h4>
        @if (count($diseases) === 0)
            <p>No diseases! Make sure load the database.</p>
        @endif
        <table>
            @foreach ($diseases as $disease)
                <tr>
                    <td>{{ $disease }}</td>
                </tr>
            @endforeach
        </table>
    </body>
</html>
