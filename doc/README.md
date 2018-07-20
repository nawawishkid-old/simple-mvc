# Framework

## Architectural components
### App
- A wrapper of all the following components.
- Bundles all other components to make it works together.

### Request
- Receives client's request.

### Router
- Receives client's request from **Request**.
- Map **Request** to specific **Controller** action.
- Sends **Request** to **Middleware** or **Controller**.

### Middleware
- Receives **Request** from **Router**.
- Manipulate **Request** e.g. authentication, authorization.
- Sends **Request** to **Controller** or.
- Sends data based on **Request** to **View** to format, gets back, then makes a **Response** for client.

### Controller
- Application's decision maker.
- Validates input from **Request** for **Model** if necessary.
- Controls its **Model** behavior.
- Controls **View** behavior.
- Gets data from **Model** then send it to **View** to format.
- Sends formatted data to **Response**.

### Model
- Manages its data in database table in accordance.

### View
- Format data given by **Controller** or **Middleware**.

### Response
- Makes a response for client based on data given by **Controller** or **Middleware**.

---

## Process flow
```
+-------+      +---------+                       +----------+
| START | ---> | REQUEST |                       | RESPONSE |
+-------+      +---------+                       +----------+
                    |                                 ^
                    |                                 |
                    v                                 |
               +--------+                             |
               | ROUTER | ------------+               |
               +--------+             |               |
                    |                 v               |
                    |           +------------+        |
                    |           | MIDDLEWARE | -----> +
                    |           +------------+        |
                    |                 |               |
                    |                 |               |
                    + <---------------+               |
                    |                                 |
                    |                                 |
                    |          +----------------------+
                    v          |
              +---------------------+            +------+            +----------+
              |     CONTROLLER      | <--------> | VIEW | <--------> | TEMPLATE |
              +---------------------+            +------+            +----------+
                    ^
                    |
                    |
                    v
                +-------+
                | MODEL |
                +-------+
                    ^
                    |
                    |
                    v
               +----------+
               | DATABASE |
               +----------+

```

---

