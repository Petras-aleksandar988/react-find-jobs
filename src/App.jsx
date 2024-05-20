import { Route,createBrowserRouter,createRoutesFromElements, RouterProvider  } from "react-router-dom"
import HomePage from "./pages/HomePage"
import Jobs from "./components/Jobs"
import NavbarLayout from "./layouts/NavbarLayout"


function App() {
  const router = createBrowserRouter(
  createRoutesFromElements (
  <Route path="/"  element={<NavbarLayout />} >
    <Route path="/"  element={<HomePage />} />
    <Route path="/jobs"  element={<Jobs />} />
  </Route>

)
  );
  return  < RouterProvider router={router} />
  
};

export default App