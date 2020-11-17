<html lang="en">
    <body>
        <h1>Hello there!</h1>

        <h4>Neo4J Diseases:</h4>
        @if (count($neo4jDiseases) === 0)
            <p>No diseases! Make sure load the database.</p>
        @endif
        <table>
            @foreach ($neo4jDiseases as $disease)
                <tr>
                    <td>{{ $disease }}</td>
                </tr>
            @endforeach
        </table>
        <h4>MongoDB Diseases:</h4>
        @if (count($mongoDiseases) === 0)
            <p>No diseases! Make sure load the database.</p>
        @endif
        <table>
            @foreach ($mongoDiseases as $disease)
                <tr>
                    <td>{{ $disease }}</td>
                </tr>
            @endforeach
        </table>
    </body>
</html>
