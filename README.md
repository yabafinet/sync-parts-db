## About
**SyncPartsDB** is a command line tool for synchronizing data from database A to database B, regardless of their table structure.

### Requirements

SyncPartsDB requires PHP version 8.1 or greater.

### Installation

```
# Download using curl
curl -OL https://yabafinet.github.io/sync-parts-db/syncpartdb.phar

```

### Usage Instruction


```bash
# Run all sync files in path:
php application sync run {dir}
```

```bash
# Run specific file configuration:
php application sync:now start {path/file.php}
```

```bash
# Check synchronization status:
php application sync:now status {dir}
```
